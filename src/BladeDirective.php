<?php

namespace Bfg\Emitter;

class BladeDirective
{
    public static function verifiedJsResponse(array $options = [])
    {
        return response(static::verifiedJs($options))
            ->header('Content-Type', 'application/javascript');
    }

    public static function verifiedJs(array $options = [])
    {
        $js = file_get_contents(public_path('vendor/emitter/emitter.js'));
        $js .= "(function () {".static::jsInstanceGenerator($options)."})();";

        return $js;
    }

    public static function jsInstanceGenerator(array $options = [])
    {
        return "window.messageConfigure(".json_encode(static::makeDefaultOptions($options)).");";
    }

    public static function directiveScripts($expression)
    {
        $class = static::class;
        return <<<HTML
<script src="<?php echo asset('vendor/emitter/emitter.js'); ?>"></script>
<script type='text/javascript'><?php echo \\$class::jsInstanceGenerator($expression); ?></script>
HTML;
    }

    public static function directiveInline($expression)
    {
        $class = static::class;
        return <<<HTML
<script type='text/javascript'><?php echo \\$class::verifiedJs($expression); ?></script>
HTML;
    }

    protected static function makeDefaultOptions(array $options = [])
    {
        if (!isset($options['domain'])) {
            $options['domain'] = url('');
        }

        if (
            !isset($options['headers']['Authorization'])
            && $authHeader = request()->header('Authorization')
        ) {
            $options['headers']['Authorization'] = $authHeader;
        }

        return $options;
    }
}
