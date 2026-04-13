<!DOCTYPE html>
<html>
<head>
    <title>{{ $note->title }}</title>

    <style>
        body {
            font-family: Arial;
            background: #f5f5f5;
            padding: 40px;
        }

        .box {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="box">
    <h1>{{ $note->title }}</h1>

    <div>
        {!! $note->content !!}
    </div>
</div>

</body>
</html>