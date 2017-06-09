<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>
        <link rel="stylesheet" href="/css/app.css" />
        <script>
            window.Laravel = { csrfToken: '{{ csrf_token() }}' }
        </script>
        <meta name="_token" content="{{ csrf_token() }}"/>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="content">
                <amo-client></amo-client>
            </div>
        </div>
        <script src="/js/app.js" type="text/javascript"></script>
    </body>
</html>
