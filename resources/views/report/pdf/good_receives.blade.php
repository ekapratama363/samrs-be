<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Good Receives</title>

    <style>
        tr.border_bottom td {
            border: 0.1px solid;
        }

        .table-center {
            text-align: center;
            vertical-align: middle;
            border: 0.1px solid;
            border-collapse: collapse;
        }

        .container:after {
            content: "";
            display: table;
            clear: both;
        }

        .column {
            float: left;
            width: 50%;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="column">
            <p><b style="font-size: large;">Good Receives (Berita Acara Serah Terima)</b></p>
        </div>
        <div class="column">
            <div style="text-align: right">
                <img src="{{asset('images/rsch.png')}}" style="width:100px; height:110px"></img>
                <!-- <p>Printed by: Eka Pratama {{ date("d M Y h:i:s") }}</p> -->
                <p>Printed at: {{ date("d M Y H:i:s") }}</p>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="column">
            <table>
                <tr>
                    <td>
                        <strong>Code Delivery Order</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        {{ $do->code }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Code Reservation</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        {{ $do->reservation->code }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Request Date</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        {{ $do->reservation->updated_at }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Delivery Date</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        {{ $do->created_at }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Duration</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        {{ $do->duration }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Request Reservation by</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        {{ $do->reservation->createdBy ? $do->reservation->createdBy->fullname : '-'  }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Approved GR by</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        {{ $do->approved ? $do->approved->fullname : '-'  }}
                    </td>
                </tr>
            </table>
        </div>
        <div class="column" style="text-align: right">
            <table>
                <tr>
                    <td>
                        <strong>Plant</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        {{ $do->reservation->plant->name }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Receiver</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        {{ $do->reservation->room_receiver->name }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Responsible Person</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        {{ $do->reservation->room_receiver->responsible ? $do->reservation->room_receiver->responsible->fullname : '-' }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Goods Receive Date</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        {{ $do->updated_at }}
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <br> <br>
    <table width="100%" class="table-center">
        <thead>
            <tr class="border_bottom">
                <td style="widtd: 30px">No</td>
                <td>Material</td>
                <td>Request Quantity</td>
                <td>Delivery Quantity</td>
                <td>UoM</td>
                <td>Supply Form</td>
            </tr>
        </thead>
        <tbody>
            @foreach($do->reservation->details as $detail)
            <tr class="border_bottom">
                <td>{{ $loop->iteration }}</td>
                <td style="text-align: left">{{ $detail->material->material_code }} - {{ $detail->material->description }}</td>
                
                <td>
                    {{ $detail->quantity }} 

                    @if ($detail->material->quantity_uom > 1)

                    {{ $detail->material->uom ? $detail->material->uom->name : '-' }}
                    isi
                    ({{ $detail->quantity * $detail->material->quantity_uom }})

                    @endif
                </td>
                
                <td>
                    {{ $detail->delivery_quantity }} 

                    @if ($detail->material->quantity_uom > 1)

                    {{ $detail->material->uom ? $detail->material->uom->name : '-' }}
                    isi
                    ({{ $detail->delivery_quantity * $detail->material->quantity_uom }})

                    @endif
                </td>
                
                <td>{{ $detail->material->uom ? $detail->material->uom->name : '-' }}</td>

                @if ($do->reservation->vendor) 
                    <td>{{ $do->reservation->vendor->name }}</td>
                @elseif ($do->reservation->room_sender)
                    <td>{{ $do->reservation->room_sender->name }}</td>
                @else
                    <td></td>
                @endif
            </tr>
                @if (count($detail->do_detail->serial_numbers) > 0)
                    <tr style="margin-bottom: 0px; padding-bottom: 0px">
                        <td colspan="6" align="justify" style="margin-bottom: 0px; padding-bottom: 0px; border:none">
                            <strong>Serial Numbers: </strong>
                            <span style="margin-top:5px; margin-bottom: 0px; padding-bottom: 0px; text-align: justify;">
                                @foreach($detail->do_detail->serial_numbers as $serial)
                                    {{ $serial->serial_number }},
                                @endforeach
                            </span>
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</body>

</html>