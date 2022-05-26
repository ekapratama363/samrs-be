
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="{{ asset('css/qrcode.css') }}" rel="stylesheet">
    <title>Qr code m24</title>
</head>

<body leftmargin="2" topmargin="2" marginwidth="2" marginheight="2">
    <!-- <table style="width:65mm;margin:2.5mm 1mm 2mm 1mm;page-break-after: always"> -->


    @foreach($stock_histories as $stock_history)
    <table style="width:65mm;margin:2.5mm 1mm 2mm 1mm;">
        <tbody border="1">
            <tr style="border-bottom:1px solid #000;position:relative;">
                <td colspan="2">
                    <h1 class="title"
                        style="text-align:centre; font-size:8px; font-weight:bold; color: #000000; margin:0 0 0 0px; ">
                        <div style="margin-bottom:1px;">{{appsetting('COMPANY_NAME')}}</div>

                    </h1>
                </td>
            </tr>
            <tr>
                <td style="width:50px; padding:5px 3px 0px 5px;  text-align: center">
                    
                    {{--
                        <img style="display:block;margin:0"
                            src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(56)->generate($stock_history->code)) !!} ">
                    --}}

                    <div style="display:block;margin:0">  
                    @php  
                        echo str_replace($remove_text, '', QrCode::format('svg')->size(55)->generate($stock_history->code)) 
                    @endphp
                    </div>
                </td>
                <td style="padding:5px 3px 0px 3px; vertical-align:top; width:100%">
                    <table class="tl" style="font-size:10px;width:100%;color: #000000; margin:10 10 10 10px;">
                        <tr>
                            <td>
                                {{$stock_history->stock->material->material_code}}
                            <td>
                        </tr>
                        <tr>
                            <td>
                                {{$stock_history->stock->material->classification->name}}
                            <td>
                        </tr>
                        <tr>
                            <td style="width:100%;padding-right: 5px">
                                SN : {{$stock_history->serial_number ? $stock_history->serial_number : '-'}}
                            <td>
                        </tr>
                        <tr>
                            <td>
                                {{$stock_history->stock->room->name}}
                            <td>
                        </tr>
                        <tr>
                            <td align="right">
                                <div style="font-size:10px; font-weight: bold">{{$stock_history->code}}</div>
                            <td>
                        </tr>

                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    @endforeach

</body>
</html>