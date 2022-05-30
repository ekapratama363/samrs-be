<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="{{ asset('css/qrcode.css') }}" rel="stylesheet">
    <title>Qr code s24</title>
</head>

<body leftmargin="2" topmargin="2" marginwidth="2" marginheight="2">
    <style type="text/css">

        .tl td {
            font-size: 9px;
            line-height: 9px;
        }

        li {
            line-height: 14px;
            list-style-type: inside;
            display: list-item
        }
        
        td,
        th {
            border: none;
            vertical-align: top;
            text-align: center;
            line-height: 11px;
        }
    </style>

    <!-- set width = 25mm -->
    @foreach($stock_details as $stock_detail)
    <table style="width:24mm;margin:2.5mm 0mm 1mm 0mm;">
        <tbody>
            <tr>
                <td style="padding:0mm 0px 0px 0px; text-align: center">
                    <div style="display:inline;margin:0">  
                        {!! str_replace($remove_text, '', QrCode::size(55)->generate($stock_detail->code)) !!}
                    </div>
                </td>

            </tr>
            <tr>
                <td>
                    <div style="font-size:8px; font-weight: bold">{{$stock_detail->code}}</div>
                <td>
            </tr>
            <tr>
                <td>
                    <div style="font-size:8px;"></div>
                <td>
            </tr>
        </tbody>
    </table>
    @endforeach

</body>

</html>