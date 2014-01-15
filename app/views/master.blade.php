<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    
    <title>Invoice Ninja {{ isset($title) ? $title : '' }}</title>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}" type="text/javascript"></script>  
    <script type="text/javascript">
    window.onerror = function(e) {
      try {
        $.ajax({
          type: 'GET',
          url: '{{ URL::to('log_error') }}',
          data: 'error='+e+'&url='+window.location
        });     
      } catch(err) {}
      return false;
    }
    </script>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
    
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/bootstrap/dist/css/bootstrap.min.css') }}"/> 

    @yield('head')

  </head>

  <body>

  @if (App::environment() == ENV_PRODUCTION)
  <script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-46031341-1');
    ga('send', 'pageview');
  </script>
  @endif

    @yield('body')

  </body>

</html>