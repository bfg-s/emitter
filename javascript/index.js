if (window.Alpine) {
    throw "This extension must be initialized before Alpine.";
}

const metaPrefix = document.querySelector(`meta[name=emitter-prefix]`);
const token = document.head.querySelector('meta[name=csrf-token]');
const queryState = {};
let web_id = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
let locationSearch = location.search;
let verifyUrl = "/emitter/verify";
let messageUrl = "/emitter/message";
let message = "";
let errors = {};
let status = 0;
let errCount = 0;

const options = {
    domain: location.origin,
    headers: {},
};

if (metaPrefix && metaPrefix.content) {
    messageUrl = "/" + metaPrefix.content + messageUrl;
    verifyUrl = "/" + metaPrefix.content + verifyUrl;
}

const showExpiredMessage = () => {
    errCount = 0;
    confirm(
        'This page has expired due to inactivity.\nWould you like to refresh the page?'
    ) && window.location.reload()
}

const restoreTocken = () => {
    return new Promise(function (resolve, reject) {
        let xhrRestore = new XMLHttpRequest();
        xhrRestore.open('GET', options.domain+verifyUrl);
        xhrRestore.setRequestHeader("X-Emitter-Message", web_id);
        Object.keys(options.headers).map(k => xhrRestore.setRequestHeader(k, options.headers[k]));
        xhrRestore.onload = function () {
            if (this.status >= 200 && this.status < 300) {
                token.content = xhrRestore.response;
                resolve();
            } else {
                showExpiredMessage();
                reject();
            }
        };
        xhrRestore.onerror = function () {
            showExpiredMessage()
            reject();
        };
        xhrRestore.send();
    });
};

const makeEmitterRequest = (name, data) => {
    return new Promise(function (resolve, reject) {
        if (errCount > 10) {
            showExpiredMessage();
            return ;
        }
        let messageXhr = new XMLHttpRequest();
        const $params = Object.keys(queryState).map(k => `${k}=${encodeURIComponent(queryState[k])}`).join('&');
        messageXhr.open('POST', options.domain+messageUrl + `/${name}` + ($params ? `${locationSearch?`${locationSearch}&`:'?'}${$params}` : locationSearch));
        messageXhr.setRequestHeader("Cache-Control", "no-cache");
        messageXhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        if (token) {
            messageXhr.setRequestHeader("X-CSRF-TOKEN", token.content);
        }
        messageXhr.setRequestHeader("X-Emitter-Message", web_id);
        Object.keys(options.headers).map(k => messageXhr.setRequestHeader(k, options.headers[k]));
        messageXhr.onload = function () {
            if (this.status >= 200 && this.status < 300) {
                errCount = 0;
                resolve(messageXhr.response);
            } else if (this.status === 419) {
                ++errCount;
                restoreTocken().then(() => {
                    makeEmitterRequest(name, data).then((r) => {
                        resolve(r);
                    })
                });
            } else {
                reject({status: this.status, statusText: messageXhr.statusText});
            }
        };
        messageXhr.onerror = function () {
            reject({status: this.status, statusText: messageXhr.statusText});
        };
        if (data && typeof data === 'object') {
            const formData = new FormData();
            Object.keys(data).map(k => {
                if (data[k] !== undefined && data[k] !== null) {
                    formData.append(k, data[k]);
                }
            })
            messageXhr.send(formData);
        } else {
            messageXhr.send();
        }
    });
};

window.messageConfigure = (config) => {
    Object.keys(config).map(k => options[k] = config[k])
};

window.$message = async (name, data) => {
    if (!name) throw "Enter a message name!";
    await makeEmitterRequest(name, data);
};

window.VueMessageMutator = {
    computed: {
        $message () {
            return window.$message;
        }
    }
};

document.addEventListener('alpine:init', () => {
    window.Alpine.magic('message', () => (...attrs) => {
        return window.$message(...attrs);
    });
});
