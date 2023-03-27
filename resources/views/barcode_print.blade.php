<!DOCTYPE html>
<head>
    <title>Print Barcode</title>
</head>
<body onload="window.print()" style="text-align:center;">
    <img width="300px" src="data:image/png;base64,{{ $barcode }}" />
</body>
</html>