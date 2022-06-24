<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation</title>

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
            <p><b style="font-size: large;">Reservation</b></p>

            <table>
                <tr>
                    <td>
                        <strong>Code Reservation</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        {{ $reservation->code }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Plant</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        {{ $reservation->plant ? $reservation->plant->name : '-' }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Receive</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        {{ $reservation->room_receiver ? $reservation->room_receiver->name : '-' }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Request By</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        {{ $reservation->createdBy ? $reservation->createdBy->fullname : '-' }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Reservation Date</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        {{ $reservation->created_at }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Update By</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        {{ $reservation->updatedBy ? $reservation->updatedBy->fullname : '-' }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Update Date</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        {{ $reservation->updated_at }}
                    </td>
                </tr>
            </table>
        </div>
        <div class="column">
            <div style="text-align: right">
                <img src="{{asset('images/rsch.png')}}" style="width:100px; height:110px"></img>
                <!-- <p>Printed by: Eka Pratama </p> -->
                <p>Printed at: {{ date("d M Y h:i:s") }}</p>
            </div>
        </div>
    </div>

    <br> <br>
    <table width="100%" class="table-center">
        <thead>
            <tr class="border_bottom">
                <td style="widtd: 30px">No</td>
                <td>Material</td>
                <td>Quantity</td>
                <td>UoM</td>
                <td>Supply Form</td>
            </tr>
        </thead>
        <tbody>
            @foreach($reservation->details as $detail)
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
                <td>{{ $detail->material->uom ? $detail->material->uom->name : '-' }}</td>
                @if ($reservation->vendor) 
                    <td>{{ $reservation->vendor->name }}</td>
                @elseif ($reservation->room_sender)
                    <td>{{ $reservation->room_sender->name }}</td>
                @else
                    <td></td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
    <html-separator/>
</body>

</html>