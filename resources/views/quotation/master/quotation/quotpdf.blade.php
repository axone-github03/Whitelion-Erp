<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Whitelion</title>
    <style type="text/css">
        * {
            font-family: sans-serif !important;
        }

        body {
            counter-reset: list;
        }

        table {
            font-size: x-small;
            width: 100%;
            table-layout: fixed;
        }

        .additional_info_ckeditor table {
            border-collapse: collapse;
        }

        .page-break {
            width: 100%;
            /* min-height: 100%; */
            background: white;
            padding-top: 30px;
            page-break-inside: avoid;
            page-break-after: auto;
        }

        .page-break-before-new {
            width: 100%;
            /* min-height: 100%; */
            background: white;
            padding-top: 30px;
            page-break-before: auto;
        }

        .header-page-break {
            width: 100%;
            background: white;
            page-break-inside: avoid;
            page-break-before: always;
        }

        .room-page-break {
            page-break-inside: avoid;
            page-break-after: always;
        }

        .board-page-break {
            page-break-inside: avoid;
            page-break-after: auto;
            /* page-break-before: initial; */
        }

        @page {
            margin: 0px;
        }

        .page-body,
        .page-footer {
            margin: 30px 30px 0px 30px;
        }

        .mb0 {
            margin-bottom: 0px !important;
        }

        .mt10 {
            margin-top: 10px !important;
        }

        .mt5 {
            margin-top: 5px !important;
        }

        .mt2 {
            margin-top: 2px !important;
        }

        .mb5 {
            margin-bottom: 5px !important;
        }

        .mb10 {
            margin-bottom: 10px !important;
        }

        .mr10 {
            margin-right: 10px !important;
        }

        .pr50 {
            padding-right: 50px !important;
        }

        .pl50 {
            padding-left: 50px !important;
        }

        .pb30 {
            padding-bottom: 30px !important;
        }

        .fbold {
            font-weight: bold;
        }

        .regularborder {
            border: solid 1px #000;
        }

        .p10-15 {
            padding: 8px 0px;
        }

        .p5-15 {
            padding: 5px 15px;
        }

        .p5-0 {
            padding: 5px 0px;
        }

        tr.border {
            border: 1px solid #000;
        }

        .bl {
            border-left: 1px solid #dfdfdf;
        }

        .br {
            border-right: 1px solid #dfdfdf;
        }

        .bt {
            border-top: 1px solid #dfdfdf;
        }

        .bb {
            border-bottom: 1px solid #dfdfdf;
        }

        .border-top-left-radius {
            border-top-left-radius: 10px;
        }

        .border-top-right-radius {
            border-top-right-radius: 10px;
        }

        .p15 {
            padding: 15px;
        }

        .w-100 {
            width: 100%;
        }

        .container {
            width: 30px;
            height: 30px;
            position: relative;
            border: 1px solid #000;
            border-radius: 7px;
        }

        .container .img1 {
            width: 10px;
            position: absolute;
            top: 25%;
            left: 25%;
            transform: translateX(-50%) translateY(-50%);
        }

        .container .img2 {
            width: 12px;
            position: absolute;
            top: 38%;
            left: 38%;
            transform: translateX(-50%) translateY(-50%);
        }

        .container .img3 {
            width: 3px;
            position: absolute;
            top: 18%;
            left: 18%;
            transform: translateX(-50%) translateY(-50%);
        }

        .title_room {
            border-radius: 25px;
            background-color: black;
            color: white;
            text-align: center;
            font-size: 25px;
            font-weight: bold;
            width: fit-content;
            padding: 2px 10px 2px 10px;
            margin-top: -28px;
            margin-left: 15px;
        }

        .txtbold {
            color: #424242;
            font-weight: bold;
        }

        .tac {
            text-align: center;
        }

        .tal {
            text-align: left;
        }

        .border-top {
            border-top: 1px solid #8b8a8a;
        }

        .border-bottom {
            border-bottom: 1px solid #8b8a8a;
        }

        .border-left {
            border-left: 1px solid #8b8a8a;
        }

        .border-right {
            border-right: 1px solid #8b8a8a;
        }

        .pt6 {
            padding-top: 6px;
        }

        .pb6 {
            padding-bottom: 6px;
        }

        .pr2 {
            padding-right: 2px;
        }

        .pl2 {
            padding-left: 2px;
        }

        .bdcoll {
            border-collapse: collapse;
        }

        .border {
            border: 1px #000 !important;
        }

        .border-t-l-radius {
            border-top-left-radius: 7px !important;
        }

        .border-b-l-radius {
            border-bottom-left-radius: 7px !important;
        }

        .border-t-r-radius {
            border-top-right-radius: 7px !important;
        }

        .border-b-r-radius {
            border-bottom-right-radius: 7px !important;
        }

        .border-s-n {
            border-style: solid none !important;
        }

        .border-s-n-s-s {
            border-style: solid none solid solid !important;
        }

        .border-s-s-s-n {
            border-style: solid solid solid none !important;
        }

        .border-s-n-s-s {
            border-style: solid none solid solid !important;
        }

        .border-n-n-n-s {
            border-style: none none none solid !important;
        }

        .border-n-n-s-n {
            border-style: none none solid none !important;
        }

        .border-s-n-n-n {
            border-style: solid none none none !important;
        }

        .border-s-n-n-s {
            border-style: solid none none solid !important;
        }

        .border-s-s-n-n {
            border-style: solid solid none none !important;
        }

        .border-n-s-n-n {
            border-style: none solid none none !important;
        }

        .border-none {
            border: 0px;
        }

        thead.report-header {
            display: table-header-group;
            margin-top: 20px;
        }

        tr.spacing td {
            padding-top: 10px !important;
            padding-bottom: 10px !important;
        }

        .callcenter-icon {
            padding: 15px;
            background-color: #000;
            float: left;
            border-radius: 50%;
            margin: 10px 10px 10px 10px;
        }

        .appendixmark {
            font-size: 8pt;
            border: 1px solid #bb6161;
            border-radius: 15px;
            background-color: #bb6161;
            position: relative;
            top: 0px;
            padding: 0px 4px 0px 4px;
            color: white;
        }

        .StepProgress {
            position: relative;
            padding-left: 45px;
            list-style: none;
        }

        .StepProgress::before {
            display: inline-block;
            content: '';
            position: absolute;
            top: 0;
            left: 15px;
            width: 10px;
            height: 55%;
            border-left: 2px dotted #CCC;
        }

        .StepProgress-item:not(:last-child) {
            padding-bottom: 20px;
        }

        .StepProgress-item {
            position: relative;
        }

        .StepProgress-item::before {
            display: inline-block;
            content: '';
            position: absolute;
            left: -30px;
            height: 100%;
            width: 10px;
        }

        .StepProgress-item::after {
            content: counter(list);
            display: inline-block;
            position: absolute;
            top: 0;
            left: -45px;
            padding: 5px 10px 5px 10px;
            border: 2px solid #CCC;
            border-radius: 50%;
            background-color: #FFF;
        }

        .StepProgress strong {
            display: block;
        }

        li::before {
            counter-increment: list;
        }

        .StepProgress {

            &-item {
                position: relative;

                &:not(:last-child) {
                    padding-bottom: 20px;
                }

                &::before {
                    display: inline-block;
                    content: '';
                    position: absolute;
                    left: -30px;
                    height: 100%;
                    width: 10px;
                }

                &::after {
                    content: '';
                    display: inline-block;
                    position: absolute;
                    top: 0;
                    left: -37px;
                    width: 12px;
                    height: 12px;
                    border: 2px solid #CCC;
                    border-radius: 50%;
                    background-color: #FFF;
                }
            }
        }

        .StepProgress-item.is-done::before {
            border-left: 2px solid green;
        }

        .StepProgress-item.is-done::after {
            content: counter(list);
            font-size: 10px;
            color: #FFF;
            text-align: center;
            border: 2px solid green;
            background-color: green;
        }

        .customer_city {
            border: 1px solid gray;
            border-radius: 25px;
            font-size: 16px;
            margin: 0px 0px;
            width: 44px;
            display: inline-block;
            font-family: Arial;
            text-align: center;
            position: relative;
            top: 7px;
            padding: 1px 13px 1px 13px;
        }

        .customer_div {
            border: 1px solid gray;
            border-radius: 7px;
            padding: 5px;
            width: 100%;
            display: inline-block;
        }

        .customer_img {
            /* width: 15%; */
            display: inline-block;
            margin-left: 1px;

        }

        .customer_name {
            width: 80%;
            display: inline-block;
            top: 20px;
            margin-left: 10px;
            position: relative;
        }

        .customer_content {
            font-size: 14px;
            /* width: 100%; */
            margin-top: 10px;
        }

        .warranty_step {
            font-size: 17px;
            font-weight: 0;
            top: 5px;
            position: relative;
        }

        .warranty_img_div1 {
            position: absolute;
            bottom: 33%;
            left: 50%;
        }

        .warranty_img_div2 {
            position: absolute;
            bottom: 23%;
            left: 50%;
        }

        .warranty_img {
            width: 50px;
            height: 50px;
        }

        a {
            text-decoration: none;
            color: #424242;
        }

        .icons a {
            aspect-ratio: 3/2;
            object-fit: contain;
        }
    </style>

</head>

<body>
    {{--    -------------------- done -------------------- --}}
    {{-- 1 page  --}}
    <div style="margin: 0px !important; padding: 0px !important;">
        <div class="page-header">
            <div
                style="background-color: black;padding-top: 20px;padding-bottom: 20px;font-family: sans-serif;height:15%">
                <table>
                    <tr>
                        <td style="width: 70%" class="pl50">
                            <h1 style="color: white;font-size: 35px;">Smart Switches <br> Proposal
                            </h1>
                        </td>
                        <td style="width: 30%;text-align: right;vertical-align: bottom;" class="pr50"><img
                                style="height: 60px;"
                                src="https://erp.whitelion.in/assets/images/quotation_pdf/logo.svg">
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div style="padding: 30px;background-color: #f2f2f2">
            <table class="w-100">
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <table>
                            <tr>
                                <td style="width: 10%;">
                                    <img style="height: 20px;width: 20px;border:1px solid;padding: 10px;border-radius: 5px;color: gray;"
                                        src="https://erp.whitelion.in/assets/images/quotation_pdf/person.svg"
                                        class="mb10">
                                </td>
                                <td>
                                    <span style="font-size: 16px;margin: 7px 0px 5px 7px;">
                                        Client Name
                                    </span>
                                    <br>
                                    <h4 style="font-size: 16px;margin: 4px 0px 13px 7px">
                                        {{ ucfirst(trans($data['basic_detail']['customer_name'])) }}</h4>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <table>
                            <tr>
                                <td style="width: 18%;">
                                    <img style="border:1px solid;padding: 10px;border-radius: 5px;color: black;height: 20px;width: 20px;"
                                        src="https://erp.whitelion.in/assets/images/quotation_pdf/contact.svg"
                                        class="mb10">
                                </td>
                                <td>
                                    <span style="font-size: 16px;margin: 7px 0px 5px 10px;">
                                        Contact Number
                                    </span>
                                    <br>
                                    <h4 style="font-size: 16px;margin: 4px 0px 13px 10px">
                                        {{ $data['basic_detail']['customer_contact_no'] }}</h4>

                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <table>
                            <tr>
                                <td style="width: 10%;">
                                    <img style="border:1px solid;padding: 10px;border-radius: 5px;color: gray;height: 20px;width: 20px;"
                                        src="https://erp.whitelion.in/assets/images/quotation_pdf/address.svg"
                                        class="mb10">
                                </td>
                                <td>
                                    <span style="font-size: 16px;margin: 7px 0px 5px 7px;">Address</span><br>
                                    <h4 style="font-size: 14px;margin: 4px 0px 13px 7px">
                                        {{ $data['basic_detail']['final_site_address'] }}
                                    </h4>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <table>
                            <tr>
                                @if ($data['basic_detail']['inquiry_id'] != 0)
                                    <td style="width: 18%;">
                                        <img style="border:1px solid;padding: 10px;border-radius: 5px;color: black;height: 20px;width: 20px;"src="https://erp.whitelion.in/assets/images/quotation_pdf/hastag.svg"
                                            class="mb10">
                                    </td>
                                    <td>
                                        <span style="font-size: 16px;margin: 7px 0px 5px 10px;">
                                            Deal No.
                                        </span>
                                        <br>
                                        <h4 style="font-size: 16px;margin: 4px 0px 13px 10px">
                                            D{{ $data['basic_detail']['inquiry_id'] }}</h4>

                                    </td>
                                @endif
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td rowspan="2">
                        <div style="margin-left: 0px;">
                            <span
                                style="font-size: 16px;margin-bottom: 0px;color: black;font-family: sans-serif !important;display: inline-block; margin-top:20px;">Quote
                                No </span>
                            <h4 style="font-size: 16px;" class="mb0 mt5">{{ $data['basic_detail']['quotno'] }}</h4>
                        </div>

                        <div style="margin-left: 0px;">
                            <span
                                style="font-size: 16px;margin-bottom: 0px;color: black;font-family: sans-serif !important;display: inline-block; margin-top:20px;">Quote
                                Version</span>
                            <h4 style="font-size: 16px;" class="mb0 mt5">{{ $data['basic_detail']['quot_no_str'] }}
                            </h4>
                        </div>
                    </td>
                    <td rowspan="2">
                        <div style="margin-left: 0px;">
                            <span
                                style="font-size: 16px;margin-bottom: 0px;color: black;font-family: sans-serif !important;display: inline-block; margin-top:20px;">Quote
                                Date </span>
                            <h4 style="font-size: 16px;" class="mb0 mt5">{{ $data['basic_detail']['quot_date'] }}
                            </h4>
                        </div>

                        <div style="margin-left: 0px;">
                            <span
                                style="font-size: 16px;margin-bottom: 0px;color: black;font-family: sans-serif !important;display: inline-block; margin-top:20px;">Site
                                Visit Date </span>
                            @if ($data['basic_detail']['quottype_id'] == 1)
                                <h4 style="font-size: 16px;" class="mb0 mt5">{{ $data['basic_detail']['quot_date'] }}
                                </h4>
                            @else
                                <h4 style="font-size: 16px;" class="mb0 mt5">-</h4>
                            @endif
                        </div>
                    </td>
                    <td rowspan="2">
                        <div style="margin-left: 0px;">
                            <span
                                style="font-size: 16px;margin-bottom: 0px;color: black;font-family: sans-serif !important;display: inline-block; margin-top:20px;">Quote
                                Type </span>
                            <h4 style="font-size: 16px;" class="mb0 mt5">{{ $data['basic_detail']['quot_type'] }}
                            </h4>
                        </div>

                        <div style="margin-left: 0px;">
                            <span
                                style="font-size: 16px;margin-bottom: 0px;color: black;font-family: sans-serif !important;display: inline-block; margin-top:20px;">Site
                                Visit With </span>
                            @if ($data['basic_detail']['quottype_id'] == 1)
                                <h4 style="font-size: 16px;" class="mb0 mt5">{!! $data['basic_detail']['site_visit_with'] !!}</h4>
                            @else
                                <h4 style="font-size: 16px;" class="mb0 mt5">-</h4>
                            @endif
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="page-body">
            <table class="w-100">
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div style="margin-left: 0px;">
                            <span
                                style="font-size: 16px;color: black;margin-bottom: 5px;display: inline-block;">Consultant
                                Details</span>
                            <h2 style="font-size: 16px;margin: 0px">
                                {{ ucfirst(trans($data['basic_detail']['consultant_first_name'])) }}
                                {{ ucfirst(trans($data['basic_detail']['consultant_last_name'])) }}<br>{{ $data['basic_detail']['consultant_phone_number'] }}<br>{{ $data['basic_detail']['consultant_email'] }}
                            </h2>
                        </div>
                    </td>
                    @if ($data['basic_detail']['channel_partner_first_name'])
                        <td colspan="2">
                            <div style="margin-left: 0px;">
                                <span
                                    style="font-size: 16px;color: black;margin-bottom: 5px;display: inline-block;">Channel
                                    Partner Details</span>
                                <h2 style="font-size: 16px;margin: 0px">
                                    {{ ucfirst(trans($data['basic_detail']['channel_partner_first_name'])) }}
                                    {{ ucfirst(trans($data['basic_detail']['channel_partner_last_name'])) }}<br>{{ $data['basic_detail']['channel_partner_mobile_number'] }}<br>{{ $data['basic_detail']['channel_partner_email'] }}
                                </h2>
                            </div>
                        </td>
                    @endif
                </tr>
            </table>
            <table class="w-100" style="margin-top: 10%">
                <tr>
                    <td style="text-align: left">
                        <div style="width: max-content; display: inline-block;">
                            <div style="font-size: 16px;" class="tal">Available in</div>
                            <div style="font-size: 40px;font-weight: bold;" class="tal">120+</div>
                            <div style="font-size: 18px;" class="tal">Cities Across <br>INDIA </div>
                        </div>
                    </td>
                    <td style="text-align: left">
                        <div style="width: max-content; display: inline-block;">
                            <div style="font-size: 16px;" class="tal">Offering </div>
                            <div style="font-size: 40px;font-weight: bold;" class="tal">7</div>
                            <div style="font-size: 18px;" class="tal">Years of <br>Warranty </div>
                        </div>
                    </td>
                    <td style="text-align: left">
                        <div style="width: max-content; display: inline-block;">
                            <div style="font-size: 16px;" class="tal">Automated </div>
                            <div style="font-size: 40px;font-weight: bold;" class="tal">25k+</div>
                            <div style="font-size: 18px;" class="tal">Home/Offices in <br>INDIA </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="page-footer" style="margin-top: 10%">
            <div style="width:100%; font-family: sans-serif;">
                <div style="">
                    <h2 style="font-size: 22px;margin-bottom: 0;" class="mb10">Whitelion Systems Pvt Ltd </h2>

                    <span style="color: rgb(0, 0, 0);font-size: 15px;">8th floor, B wing, Unoin Heights, Bs. Lalbhai
                        Contractor
                        Stadium,
                        Maharana Pratap Road, Piplod,<br>Surat-395007 | whitelion.in </span>
                </div>

            </div>
        </div>
    </div>

    {{-- 2 page  --}}
    {{-- <div class="page-break" style="margin: 0px !important; padding: 0px !important;">
        <div class="page-header">
            <table>
                <tr>
                    <td style="width: 30%">
                        <div style="margin: 0px 30px;">
                            <h2 style="font-size: 36px;font-weight: bold;">Why <br> Whitelion?</h2>
                            <div style="text-align: left;font-family: sans-serif;font-size: 18px;">
                                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor
                                incididunt ut labore et
                                dolor magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris
                                nisi ut aliquip ex ea
                                commodo
                            </div>
                        </div>
                    </td>
                    <td style="width: 70%">
                        <div
                            style="width: 500px;height: 400px;display:inline-block ;background-color: gray;position: relative;position:relative;margin-top: -20px;margin-left: 10%">
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="page-body" style="margin-top: 40px">
            <table>
                <tr>
                    <td>
                        <div style="margin-right: 10px">
                            <span style="font-size: 16px;">Available in</span><br>
                            <span style="font-size: 40px;font-weight: bold;">75+</span><br>
                            <span style="font-size: 18px;">Cities Across <br>INDIA </span>
                        </div>
                    </td>
                    <td>
                        <div style="margin-right: 0px">
                            <span style="font-size: 16px;">Offering </span><br>
                            <span style="font-size: 40px;font-weight: bold;">7</span><br>
                            <span style="font-size: 18px;">Years of <br>AirCare </span>
                        </div>
                    </td>
                    <td>
                        <div style="margin-right: 10px">
                            <span style="font-size: 16px;">Automated </span><br>
                            <span style="font-size: 40px;font-weight: bold;">15k+</span><br>
                            <span style="font-size: 18px;">Home/Offices in <br>INDIA </span>
                        </div>
                    </td>
                    <td>
                        <div style="margin-right: 10px">
                            <span style="font-size: 16px;">Available in </span><br>
                            <span style="font-size: 40px; font-weight: bold;">75+</span><br>
                            <span style="font-size: 18px;">Cities across <br>INDIA </span>
                        </div>
                    </td>
                </tr>
            </table>
            <h5
                style="font-family: sans-serif;font-size: 42px;font-weight: bold;margin: 15px 0px; display: inline-block;">
                Our Product Range</h5>
            <table>
                <tr>
                    <td>
                        <div style="height: 150px ;width:200px;background-color: gray;">
                        </div>
                        <h2 style="font-size:24px ;font-weight: bold;">Mocha</h2>
                        <div style="font-size: 18px; text-align: left;width: 190px;">Lorem ipsum dolor sit
                            amet,consectetur
                            adipisicing
                            elit, sed do eiusmod tempor</div>

                        <div
                            style="border: 1px solid gray;border-radius: 25px; font-size: 16px;  margin: 15px;width: 130px; font-family: Arial;text-align: center;margin-left: 0;padding: 8px 5px 8px 10px;">
                            <table>
                                <tr>
                                    <td>
                                        <span style="font-size: 16px;">bit.ly/mocha</span>
                                    </td>
                                    <td>
                                        <img src="https://erp.whitelion.in/assets/images/quotation_pdf/link.svg"
                                            alt="" style="width: 15px;margin-left:35px;">
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div style="">
                            <table>
                                <tr>
                                    <td>
                                        <img src="https://erp.whitelion.in/assets/images/quotation_pdf/barcode.svg"
                                            alt="" style="width: 45px;margin-top: 10px;">
                                    </td>
                                    <td>
                                        <span style="margin-left: -70px;font-size: 12px;display: inline-block;">Scan to
                                            see<br>full range</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                    <td>
                        <div
                            style="border: 1px solid red;border-radius: 7px;padding: 10px;margin-top: -10px;width: 200px;display: inline-block;">
                            <div style="height: 150px ;width:200px;background-color: gray;">
                            </div>
                            <h2 style="font-size:24px ;font-weight: bold;">Mocha</h2>
                            <div style="font-size: 18px; text-align: left;width: 190px;">Lorem ipsum dolor sit
                                amet,consectetur
                                adipisicing
                                elit, sed do eiusmod tempor</div>

                            <div
                                style="border: 1px solid gray;border-radius: 25px; font-size: 16px;  margin: 15px;width: 130px; font-family: Arial;text-align: center;margin-left: 0;padding: 8px 5px 8px 10px;">
                                <table>
                                    <tr>
                                        <td>
                                            <span style="font-size: 16px;">bit.ly/mocha</span>
                                        </td>
                                        <td>
                                            <img src="https://erp.whitelion.in/assets/images/quotation_pdf/link.svg"
                                                alt="" style="width: 15px;margin-left:35px;">
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div style="">
                                <table>
                                    <tr>
                                        <td>
                                            <img src="https://erp.whitelion.in/assets/images/quotation_pdf/barcode.svg"
                                                alt="" style="width: 45px;margin-top: 5px;">
                                        </td>
                                        <td>
                                            <span
                                                style="margin-left: -50px;font-size: 12px;display: inline-block;">Scan
                                                to see<br>full range</span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="height: 150px ;width:200px;background-color: gray;">
                        </div>
                        <h2 style="font-size:24px ;font-weight: bold;">Mocha</h2>
                        <div style="font-size: 18px; text-align: left;width: 190px;">Lorem ipsum dolor sit
                            amet,consectetur
                            adipisicing
                            elit, sed do eiusmod tempor</div>

                        <div
                            style="border: 1px solid gray;border-radius: 25px; font-size: 16px;  margin: 15px;width: 130px; font-family: Arial;text-align: center;margin-left: 0;padding: 8px 5px 8px 10px;">
                            <table>
                                <tr>
                                    <td>
                                        <span style="font-size: 16px;">bit.ly/mocha</span>
                                    </td>
                                    <td>
                                        <img src="https://erp.whitelion.in/assets/images/quotation_pdf/link.svg"
                                            alt="" style="width: 15px;margin-left:35px;">
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div style="">
                            <table>
                                <tr>
                                    <td>
                                        <img src="https://erp.whitelion.in/assets/images/quotation_pdf/barcode.svg"
                                            alt="" style="width: 45px;margin-top: 10px;">
                                    </td>
                                    <td>
                                        <span style="margin-left: -70px;font-size: 12px;display: inline-block;">Scan to
                                            see<br>full range</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div> --}}

    @if ($data['pdf_permission']['area_page_visible'] != 0)
        <div class="page-break" style="margin: 0px !important; padding: 0px !important;">
            <div class="page-header header-page-break">
                <div
                    style="background-color: black;padding-top: 20px;padding-bottom: 20px;font-family: sans-serif;height:15%">
                    <table>
                        <tr>
                            <td style="width: 70%" class="pl50">
                                <h1 style="color: white;font-size: 35px;">Room/Area wise<br>Summary
                                </h1>
                            </td>
                            <td style="width: 30%;text-align: right;vertical-align: bottom;" class="pr50"><img
                                    style="height: 60px;"
                                    src="https://erp.whitelion.in/assets/images/quotation_pdf/logo.svg">
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="page-body">
                @if ($data['pdf_permission']['area_summary_visible'] != 0)
                    {{-- <h2 style="font-size: 24px;font-weight: bold;margin-top: 0px;">
                        {{ ucfirst(trans($data['basic_detail']['customer_name'])) }}</h2> --}}
                    <table
                        style="margin-top: 0px; width: 100%;font-size: 18px;border-collapse: separate; border-spacing: 0;">
                        <tbody>
                            <tr style="text-align: left;font-weight: bold;">
                                <td style="padding: 10px 25px;">Name</td>
                                <td>Whitelion(Rs.)</td>
                                <td>Others(Rs.)</td>
                                <td>Total(Rs.)</td>
                            </tr>

                            @foreach ($data['area_summary']['area_list'] as $area_list)
                                <tr>
                                    <td
                                        style="padding: 10px 25px;border-top-left-radius: 7px;border-bottom-left-radius: 7px; border: solid 1px #000;border-style:solid none solid solid ;">
                                        {{ ucfirst(trans($area_list['room_name'])) }}</td>
                                    <td style="border: solid 1px #000;border-style: solid none;">
                                        {{ numCommaFormat($area_list['room_total_whitelion_net_amount']) }}</td>
                                    <td style="border: solid 1px #000;border-style: solid none;">
                                        {{ numCommaFormat($area_list['room_total_other_net_amount']) }}</td>
                                    <td
                                        style="padding: 0px 5px;border-top-right-radius: 7px;border-bottom-right-radius: 7px; border: solid 1px #000;border-style: solid  solid solid none;">
                                        {{ numCommaFormat($area_list['room_total_net_amount']) }}</td>
                                </tr>
                                <tr>
                                    <td style="height: 10px;"></td>
                                <tr>
                            @endforeach


                            <tr style="font-weight: bold">
                                <td style="padding: 0px 25px;">Total</td>
                                <td>
                                    {{ numCommaFormat($data['area_summary']['area_summary_total_whitelion_amount']) }}
                                </td>
                                <td> {{ numCommaFormat($data['area_summary']['area_summary_total_other_amount']) }}
                                </td>
                                <td> {{ numCommaFormat($data['area_summary']['area_summary_total_final_amount']) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                @endif


                @if ($data['pdf_permission']['product_summary_visible'] != 0)
                    <h2 style="font-size: 24px;font-weight: bold;margin-top: 35px;display: inline-block;">Product Wise
                        summary</h2>
                    <table
                        style="margin-top: 0px; border-collapse: collapse;width: 100%;font-size: 16px;border-collapse: separate; border-spacing: 0;">
                        <tbody>
                            <tr>
                                <td></td>
                                <td colspan="5" style="text-align: center;padding-bottom: 10px;font-size: 12px">
                                    Whitelion</td>
                                <td colspan="4" style="text-align: center;padding-bottom: 10px;font-size: 12px">
                                    Others</td>
                            </tr>
                            <tr style="text-align: left;font-size: 10px">
                                <td style="padding: 0px 25px 0px 10px;width: 20%; ">Name</td>
                                <td class="p5-15 border-top" style="width: 10%;border-top-left-radius:15px;">
                                    Touch<br>On/Off</td>
                                <td class="p5-0 border-top" style="width: 10%;">Touch<br>Regulator</td>
                                <td class="p5-0 border-top" style="width: 10%;">WL<br>Plug</td>
                                <td class="p5-0 border-top" style="width: 10%;">Special</td>
                                <td class="p5-0 border-top" style="width: 10%;border-top-right-radius:15px;">
                                    WL<br>Accessories</td>
                                {{-- <td class="p5-0" style="width: 8%;">Rc2</td> --}}
                                <td class="p5-15 border-top" style="width: 10%;border-top-left-radius:15px;">
                                    Normal<br>Switch</td>
                                <td class="p5-0 border-top" style="width: 10%;">Normal<br>Regulator</td>
                                <td class="p5-0 border-top" style="width: 10%;">Normal<br>Plug</td>
                                <td class="p5-0 border-top" style="width: 10%;border-top-right-radius:15px;">Other
                                </td>
                            </tr>
                            <?php
                            $total_touch_on_off = 0;
                            $total_touch_fan_regulator = 0;
                            $total_wl_plug = 0;
                            $total_special = 0;
                            $total_wl_accessories = 0;
                            // $total_rc2 = 0;
                            $total_normal_switch = 0;
                            $total_normal_fan_regulator = 0;
                            $total_other_plug = 0;
                            $total_other = 0;
                            ?>
                            @foreach ($data['area_summary']['item_excel_summary'] as $item_excel_summary)
                                <tr style="border: 1px solid gray;border-radius: 25px;width: 100%;font-size: 14px">
                                    <td style="width: 20%;padding: 3px 25px 3px 10px ;border-top-left-radius: 10px;border-bottom-left-radius: 10px;border-style:solid none solid solid ;"
                                        class="regularborder">{{ $item_excel_summary['room_name'] }}</td>
                                    @if ($item_excel_summary['touch_on_off'])
                                        <td style="border-style: solid none;width: 10%;" class=" regularborder p5-15">
                                            {{ $item_excel_summary['touch_on_off'] }}</td>
                                    @else
                                        <td style="border-style: solid none;width: 10%;" class=" regularborder p5-15">
                                            0</td>
                                    @endif

                                    @if ($item_excel_summary['touch_fan_regulator'])
                                        <td style="border-style: solid none;width: 10%;" class=" regularborder p5-0">
                                            {{ $item_excel_summary['touch_fan_regulator'] }}</td>
                                    @else
                                        <td style="border-style: solid none;width: 10%;" class=" regularborder p5-0">0
                                        </td>
                                    @endif

                                    @if ($item_excel_summary['wl_plug'])
                                        <td style="border-style: solid none;width: 10%;" class=" regularborder p5-0">
                                            {{ $item_excel_summary['wl_plug'] }}</td>
                                    @else
                                        <td style="border-style: solid none;width: 10%;" class=" regularborder p5-0">0
                                        </td>
                                    @endif

                                    @if ($item_excel_summary['special'])
                                        <td style="border-style: solid none;width: 10%;" class=" regularborder p5-0">
                                            {{ $item_excel_summary['special'] }}</td>
                                    @else
                                        <td style="border-style: solid none;width: 10%;" class=" regularborder p5-0">0
                                        </td>
                                    @endif

                                    @if ($item_excel_summary['wl_accessories'])
                                        <td style="border-style: solid none;width: 10%;" class=" regularborder p5-0">
                                            {{ $item_excel_summary['wl_accessories'] }}</td>
                                    @else
                                        <td style="border-style: solid none;width: 10%;" class=" regularborder p5-0">0
                                        </td>
                                    @endif

                                    {{-- @if ($item_excel_summary['rc2'])
                                        <td style="border-style: solid none;width: 8%;" class=" regularborder p5-0">{{$item_excel_summary['rc2']}}</td>
                                    @else
                                        <td style="border-style: solid none;width: 8%;" class=" regularborder p5-0">0</td>
                                    @endif --}}

                                    @if ($item_excel_summary['normal_switch'])
                                        <td style="border-style: solid none;width: 10%;" class=" regularborder p5-15">
                                            {{ $item_excel_summary['normal_switch'] }}</td>
                                    @else
                                        <td style="border-style: solid none;width: 10%;" class=" regularborder p5-15">
                                            0</td>
                                    @endif

                                    @if ($item_excel_summary['normal_fan_regulator'])
                                        <td style="border-style: solid none;width: 10%;" class=" regularborder p5-0">
                                            {{ $item_excel_summary['normal_fan_regulator'] }}</td>
                                    @else
                                        <td style="border-style: solid none;width: 10%;" class=" regularborder p5-0">0
                                        </td>
                                    @endif

                                    @if ($item_excel_summary['other_plug'])
                                        <td style="border-style: solid none;width: 10%;" class=" regularborder p5-0">
                                            {{ $item_excel_summary['other_plug'] }}</td>
                                    @else
                                        <td style="border-style: solid none;width: 10%;" class=" regularborder p5-0">0
                                        </td>
                                    @endif

                                    @if ($item_excel_summary['other'])
                                        <td style="width: 10%; border-top-right-radius: 10px; border-bottom-right-radius: 10px;border-style: solid  solid solid none;"
                                            class=" regularborder p5-0">{{ $item_excel_summary['other'] }}</td>
                                    @else
                                        <td style="width: 10%; border-top-right-radius: 10px; border-bottom-right-radius: 10px;border-style: solid  solid solid none;"
                                            class=" regularborder p5-0">0</td>
                                    @endif

                                    <?php
                                    $total_touch_on_off = $total_touch_on_off + $item_excel_summary['touch_on_off'];
                                    $total_touch_fan_regulator = $total_touch_fan_regulator + $item_excel_summary['touch_fan_regulator'];
                                    $total_wl_plug = $total_wl_plug + $item_excel_summary['wl_plug'];
                                    $total_special = $total_special + $item_excel_summary['special'];
                                    $total_wl_accessories = $total_wl_accessories + $item_excel_summary['wl_accessories'];
                                    // $total_rc2 = $total_rc2 + $item_excel_summary['rc2'];
                                    $total_normal_switch = $total_normal_switch + $item_excel_summary['normal_switch'];
                                    $total_normal_fan_regulator = $total_normal_fan_regulator + $item_excel_summary['normal_fan_regulator'];
                                    $total_other_plug = $total_other_plug + $item_excel_summary['other_plug'];
                                    $total_other = $total_other + $item_excel_summary['other'];
                                    ?>
                                </tr>

                                <tr>
                                    <td style="height: 6px;"></td>
                                </tr>
                            @endforeach
                            <tr style="font-weight: bold;font-size: 14px">
                                <td style="padding: 3px 25px 3px 10px;width: 20%; ">Total</td>
                                <td class="p5-15" style="width: 10%;">{{ $total_touch_on_off }}</td>
                                <td class="p5-0" style="width: 10%;">{{ $total_touch_fan_regulator }}</td>
                                <td class="p5-0" style="width: 10%;">{{ $total_wl_plug }}</td>
                                <td class="p5-0" style="width: 10%;">{{ $total_special }}</td>
                                <td class="p5-0" style="width: 10%;">{{ $total_wl_accessories }}</td>
                                {{-- <td class="p5-0" style="width: 10%;">{{$total_rc2}}</td> --}}
                                <td class="p5-15" style="width: 10%;">{{ $total_normal_switch }}</td>
                                <td class="p5-0" style="width: 10%;">{{ $total_normal_fan_regulator }}</td>
                                <td class="p5-0" style="width: 10%;">{{ $total_other_plug }}</td>
                                <td class="p5-0" style="width: 10%;">{{ $total_other }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    @endif

    {{-- NEW UPDATE --}}
    {{-- 4 page --}}
    @if ($data['pdf_permission']['area_detailed_summary_visible'] == 1)
        <div style="margin: 0px !important; padding: 0px !important;page-break-before: auto;">
            <div class="page-header header-page-break">
                <div
                    style="background-color: black;padding-top: 20px;padding-bottom: 20px;font-family: sans-serif;height:15%">
                    <table>
                        <tr>
                            <td style="width: 70%" class="pl50">
                                <h1 style="color: white;font-size: 35px;">Room/Area wise<br>Detailed Summary
                                </h1>
                            </td>
                            <td style="width: 30%;text-align: right;vertical-align: bottom;" class="pr50"><img
                                    style="height: 60px;"
                                    src="https://erp.whitelion.in/assets/images/quotation_pdf/logo.svg">
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="page-body">
                @foreach ($data['rds_room_summary'] as $rds_room_key => $rds_room_value)
                    <div style="padding-top: {{ $rds_room_key == 0 ? '20px' : '50px' }};">
                        <h5
                            style="text-align: center;margin: 0px;padding: 5px 20px;border: 1px solid black;border-radius: 25px;margin-left: 12px;background-color: black;width: 30%;color: white;text-align: center;font-size: 24px;font-weight: bold;position: relative;">
                            {{ ucfirst(trans($rds_room_value['rds_room_name'])) }}</h5>
                        <div
                            style="border: 1px solid gray;border-radius: 8px;width: auto;margin-top: -18px;padding: 28px 12px 12px;">
                            <table style="width: 100%;font-size: 14px;border-collapse: unset;border-spacing: 0px;">
                                <thead class="report-header">
                                    <tr style="text-align: left;font-size: 14px;" class="spacing">
                                        <td class="txtbold tac" style="padding: 0px 2px;width: 9%;">Sr No.</td>
                                        <td class="txtbold" style="padding: 0px 2px;width: 40%">Product Name</td>

                                        {{-- @if ($data['pdf_permission']['area_detailed_rate_total_visible'] == 1 || $data['pdf_permission']['area_detailed_discount_visible'] == 1 || $data['pdf_permission']['area_detailed_gst_visible'] == 1) --}}
                                        {{-- @if ($data['pdf_permission']['area_detailed_rate_total_visible'] == 1) --}}
                                        <td class="txtbold tac" style="padding: 0px 2px;width: 10%">Rate(Rs)</td>
                                        {{-- @endif --}}

                                        <td class="txtbold tac" style="padding: 0px 2px;width: 6%">Qty</td>

                                        @if ($data['pdf_permission']['area_detailed_discount_visible'] == 1)
                                            <td class="txtbold tac" style="padding: 0px 2px;width: 10%;">Dis %</td>
                                        @endif

                                        @if (
                                            $data['pdf_permission']['area_detailed_discount_visible'] == 1 ||
                                                $data['pdf_permission']['area_detailed_gst_visible'] == 1)
                                            <td class="tac txtbold" style="padding: 0px 2px;width: 10%;">Tax Value
                                            </td>
                                        @endif

                                        @if ($data['pdf_permission']['area_detailed_gst_visible'] == 1)
                                            <td class="txtbold tac" style="padding: 0px 2px;width: 10%;">Gst %</td>
                                        @endif

                                        {{-- @if ($data['pdf_permission']['area_detailed_rate_total_visible'] == 1 || $data['pdf_permission']['area_detailed_discount_visible'] == 1 || $data['pdf_permission']['area_detailed_gst_visible'] == 1) --}}
                                        @if (
                                            $data['pdf_permission']['area_detailed_discount_visible'] == 1 ||
                                                $data['pdf_permission']['area_detailed_gst_visible'] == 1 ||
                                                $data['pdf_permission']['area_detailed_rate_total_visible'] == 1)
                                            <td class="tac txtbold" style="padding: 0px 2px;width: 15%;">Total(Rs)
                                            </td>
                                        @endif

                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rds_room_value['rds_room_item'] as $rds_item_key => $rds_room_item_value)
                                        <tr
                                            style="text-align: left;font-size: 14px !important;page-break-inside: avoid;">
                                            <td class="bdcoll txtbold border-top border-bottom border-left tac pr2 pl2 pt6 pb6"
                                                style="border-top-left-radius: 7px;border-bottom-left-radius: 7px;">
                                                {{ $rds_item_key + 1 }}</td>
                                            <td class="bdcoll txtbold border-top border-bottom pr2 pl2 pt6 pb6">
                                                @if ($rds_room_item_value['is_appendix'] != 0)
                                                    <span
                                                        class="appendixmark">#{{ $rds_room_item_value['is_appendix'] }}</span>
                                                @endif
                                                @if (in_array($rds_room_item_value['itemgroup_id'], [2, 4]))
                                                    {{ $rds_room_item_value['itemsubgroupname'] }}
                                                @else
                                                    {{ $rds_room_item_value['itemname'] }} -
                                                    {{ $rds_room_item_value['itemsubgroupname'] }}
                                                @endif
                                            </td>

                                            {{-- @if ($data['pdf_permission']['area_detailed_rate_total_visible'] == 1 || $data['pdf_permission']['area_detailed_discount_visible'] == 1 || $data['pdf_permission']['area_detailed_gst_visible'] == 1) --}}
                                            {{-- @if ($data['pdf_permission']['area_detailed_rate_total_visible'] == 1) --}}
                                            <td class="bdcoll txtbold border-top border-bottom tac pr2 pl2 pt6 pb6">
                                                {{ numCommaFormat(round($rds_room_item_value['rate'])) }}</td>
                                            {{-- @endif --}}

                                            @if (
                                                $data['pdf_permission']['area_detailed_rate_total_visible'] != 1 &&
                                                    $data['pdf_permission']['area_detailed_discount_visible'] != 1 &&
                                                    $data['pdf_permission']['area_detailed_gst_visible'] != 1)
                                                <td class="bdcoll tac txtbold border-top border-bottom tac pr2 pl2 pt6 pb6 border-right"
                                                    style="border-top-right-radius: 7px;border-bottom-right-radius: 7px;">
                                                    {{ $rds_room_item_value['qty'] }}</td>
                                            @else
                                                <td
                                                    class="bdcoll txtbold border-top border-bottom tac pr2 pl2 pt6 pb6">
                                                    {{ $rds_room_item_value['qty'] }}</td>
                                            @endif


                                            @if ($data['pdf_permission']['area_detailed_discount_visible'] == 1)
                                                <td class="bdcoll txtbold border-top border-bottom tac  pl2 pt6 pb6">
                                                    {{ $rds_room_item_value['discper'] }}</td>
                                            @endif

                                            @if (
                                                $data['pdf_permission']['area_detailed_discount_visible'] == 1 ||
                                                    $data['pdf_permission']['area_detailed_gst_visible'] == 1)
                                                <td
                                                    class="bdcoll txtbold border-top border-bottom tac pr2 pl2 pt6 pb6">
                                                    {{ numCommaFormat(round($rds_room_item_value['grossamount'])) }}
                                                </td>
                                                {{-- @elseif( $data['pdf_permission']['area_detailed_discount_visible'] == 0 && $data['pdf_permission']['area_detailed_gst_visible'] == 1)
                                                <td class="bdcoll txtbold border-top border-bottom tac pr2 pl2 pt6 pb6">
                                                    {{ numCommaFormat(round($rds_room_item_value['grossamount'])) }}
                                                </td>
                                            @elseif($data['pdf_permission']['area_detailed_discount_visible'] == 1 && $data['pdf_permission']['area_detailed_gst_visible'] == 0)
                                                <td class="bdcoll txtbold border-top border-bottom tac border-right pr2 pl2 pt6 pb6" style="border-top-right-radius: 7px;border-bottom-right-radius: 7px;">
                                                    {{ numCommaFormat(round($rds_room_item_value['grossamount'])) }}
                                                </td> --}}
                                            @endif

                                            @if ($data['pdf_permission']['area_detailed_gst_visible'] == 1)
                                                <td
                                                    class="bdcoll txtbold border-top border-bottom tac pr2 pl2 pt6 pb6">
                                                    {{ numCommaFormat(round($rds_room_item_value['igst_per'])) }} </td>
                                            @endif

                                            {{-- @if ($data['pdf_permission']['area_detailed_rate_total_visible'] == 1 || $data['pdf_permission']['area_detailed_discount_visible'] == 1 || $data['pdf_permission']['area_detailed_gst_visible'] == 1) --}}
                                            @if (
                                                $data['pdf_permission']['area_detailed_discount_visible'] == 1 ||
                                                    $data['pdf_permission']['area_detailed_gst_visible'] == 1 ||
                                                    $data['pdf_permission']['area_detailed_rate_total_visible'] == 1)
                                                <td class="bdcoll txtbold border-top border-bottom tac border-right pr2 pl2 pt6 pb6"
                                                    style="border-top-right-radius: 7px;border-bottom-right-radius: 7px;">
                                                    {{ numCommaFormat(round($rds_room_item_value['net_amount'])) }}
                                                </td>
                                            @endif
                                        </tr>
                                        <tr style="page-break-inside: avoid;">
                                            <td style="height: 5px"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <table class="page-break" style="padding-top: 0px !important;">
                                {{-- @if ($data['pdf_permission']['area_detailed_rate_total_visible'] == 1 || $data['pdf_permission']['area_detailed_discount_visible'] == 1 || $data['pdf_permission']['area_detailed_gst_visible'] == 1) --}}
                                {{-- @if ($data['pdf_permission']['area_detailed_rate_total_visible'] == 1) --}}
                                <tr class="txtbold" style="font-size: 14px;">
                                    <td colspan="6" style="text-align: right;padding-right: 20px;">Total</td>
                                    <td style="text-align: left;">
                                        {{ numCommaFormat($rds_room_value['rds_room_total_netamount']) }}</td>
                                </tr>
                                {{-- @endif --}}
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- 5 page --}}
    @if ($data['pdf_permission']['product_detailed_summary_visible'] != 0)
        <div class="" style="margin-top: 0px !important; padding: 0px !important;">
            <div class="page-header header-page-break">
                <div
                    style="background-color: black;padding-top: 20px;padding-bottom: 20px;font-family: sans-serif;height:15%">
                    <table>
                        <tr>
                            <td style="width: 70%" class="pl50">
                                <h1 style="color: white;font-size: 35px;">Product wise<br>Detailed Summary</h1>
                            </td>
                            <td style="width: 30%;text-align: right;vertical-align: bottom;" class="pr50"><img
                                    style="height: 60px;"
                                    src="https://erp.whitelion.in/assets/images/quotation_pdf/logo.svg">
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="page-body" style="margin-top: 0px !important;">
                @foreach ($data['room'] as $room_value)
                    <div style="border: 1px solid gray;border-radius: 10px;width: auto;height: auto;margin-top: 40px;">
                        <div style="padding:20px;">
                            <div
                                style="border: 1px solid black;border-radius: 25px; margin: 8px 0px 0px 10px;background-color: black;width: {{ 15 * strlen($room_value['room_name']) }}px;color: white;text-align: center;font-size: 20px;position: relative;bottom: 40px;font-weight: bold;">
                                <h5 style="text-align: center;margin: 0px;padding: 4px 0px;position: relative;">
                                    {{ ucfirst(trans($room_value['room_name'])) }}</h5>
                            </div>
                            <div style="margin-top: -40px">
                                <!-- header table-->
                                <table style="width:100%;" class="border-none">
                                    <tbody>
                                        <tr>
                                            <th style="padding: 0px 0px 0px 0px;width: 25%;"></th>
                                            <th style="padding: 0px 0px 0px 0px;width:25%;">Item</th>

                                            {{-- @if ($data['pdf_permission']['product_detailed_discount_visible'] == 1 || $data['pdf_permission']['product_detailed_gst_visible'] == 1 || $data['pdf_permission']['product_detailed_rate_total_visible'] == 1) --}}
                                            {{-- @if ($data['pdf_permission']['product_detailed_rate_total_visible'] == 1) --}}
                                            <th style="padding: 0px 0px 0px 0px;width: 10%;" class="tac">Rate(Rs)
                                            </th>
                                            {{-- @endif --}}

                                            <th style="padding: 0px 0px 0px 0px;width: 5%;" class="tac">Qty</th>

                                            @if ($data['pdf_permission']['product_detailed_discount_visible'] == 1)
                                                <th style="padding: 0px 0px 0px 0px;width: 10%;" class="tac">Dis %
                                                </th>
                                            @endif

                                            @if (
                                                $data['pdf_permission']['product_detailed_discount_visible'] == 1 ||
                                                    $data['pdf_permission']['product_detailed_gst_visible'] == 1)
                                                <th style="padding: 0px 0px 0px 0px;width: 10%;" class="tac">Tax
                                                    Value</th>
                                            @endif

                                            @if ($data['pdf_permission']['product_detailed_gst_visible'] == 1)
                                                <th style="padding: 0px 0px 0px 0px;width: 10%;" class="tac">Gst %
                                                </th>
                                            @endif

                                            @if (
                                                $data['pdf_permission']['product_detailed_discount_visible'] == 1 ||
                                                    $data['pdf_permission']['product_detailed_gst_visible'] == 1 ||
                                                    $data['pdf_permission']['product_detailed_rate_total_visible'] == 1)
                                                <th style="padding: 0px 0px 0px 0px;width: 10%;" class="tac">
                                                    Total(Rs)</th>
                                            @endif
                                        </tr>
                                    </tbody>
                                </table>
                                <!-- body table-->
                                @foreach ($room_value['board'] as $b_key => $board)
                                    <div class="page-break" style="padding-top: 10px !important;">
                                        <table
                                            style="text-align: left;border-collapse: separate; border-spacing: 0;width:100%;">
                                            <tbody>
                                                @foreach ($board['board_item'] as $key => $board_item)
                                                    @if ($key == 0)
                                                        <tr>
                                                            @if ($board['board_name'] == null)
                                                                <td style="width: 25%; padding: 1px 1px;vertical-align: middle;"
                                                                    class="border-s-n-n-s border border-t-l-radius tac">
                                                                    <img src="{{ getSpaceFilePath($board_item['addons_image']) }}"
                                                                        style="object-fit: contain;" width="auto"
                                                                        height="40px">
                                                                </td>
                                                            @else
                                                                <td rowspan="{{ (int) $board['board_item_count'] }}"style="width: 25%;"
                                                                    class="border-s-n-n-s border border-t-l-radius tac">
                                                                    <img src="{{ $board['board_image'] }}"
                                                                        style="margin: 10px 20px;object-fit: contain;"
                                                                        width="auto" height="70px">
                                                                </td>
                                                            @endif

                                                            <td
                                                                style="width: 25%;padding: 5px 5px;"class=" border-s-n-n-n border">
                                                                @if ($board_item['is_appendix'] != 0)
                                                                    <span
                                                                        class="appendixmark">#{{ $board_item['is_appendix'] }}</span>
                                                                @endif
                                                                {{ $board_item['itemname'] }}
                                                            </td>

                                                            {{-- @if ($data['pdf_permission']['product_detailed_discount_visible'] == 1 || $data['pdf_permission']['product_detailed_gst_visible'] == 1 || $data['pdf_permission']['product_detailed_rate_total_visible'] == 1) --}}
                                                            {{-- @if ($data['pdf_permission']['product_detailed_rate_total_visible'] == 1) --}}
                                                            <td
                                                                style="width: 10%;padding: 5px 5px;"class="tac border-s-n-n-n border">
                                                                {{ numCommaFormat($board_item['rate']) }}</td>
                                                            {{-- @endif --}}

                                                            @if (
                                                                $data['pdf_permission']['product_detailed_rate_total_visible'] != 1 &&
                                                                    $data['pdf_permission']['product_detailed_discount_visible'] != 1 &&
                                                                    $data['pdf_permission']['product_detailed_gst_visible'] != 1)
                                                                <td
                                                                    style="width: 5%;padding: 5px 5px;"class="tac border-s-s-n-n border border-t-r-radius ">
                                                                    {{ $board_item['qty'] }}</td>
                                                            @else
                                                                <td
                                                                    style="width: 5%;padding: 5px 5px;"class="tac border-s-n-n-n border">
                                                                    {{ $board_item['qty'] }}</td>
                                                            @endif

                                                            @if ($data['pdf_permission']['product_detailed_discount_visible'] == 1)
                                                                <td style="width: 10%;padding: 5px 5px;"
                                                                    class="tac border-s-n-n-n border">
                                                                    {{ $board_item['discper'] }}</td>
                                                            @endif

                                                            @if (
                                                                $data['pdf_permission']['product_detailed_discount_visible'] == 1 ||
                                                                    $data['pdf_permission']['product_detailed_gst_visible'] == 1)
                                                                <td style="width: 10%;padding: 5px 5px;"
                                                                    class="tac border-s-n-n-n border">
                                                                    {{ $board_item['taxableamount'] }}</td>
                                                                {{-- @elseif ( $data['pdf_permission']['product_detailed_discount_visible'] == 0 && $data['pdf_permission']['product_detailed_gst_visible'] == 1)
                                                                <td style="width: 10%;padding: 5px 5px;" class="tac border-s-n-n-n border"> {{ $board_item['taxableamount'] }}</td>
                                                            @elseif (
                                                                $data['pdf_permission']['product_detailed_discount_visible'] == 1 &&
                                                                    $data['pdf_permission']['product_detailed_gst_visible'] == 0)
                                                                <td style="width: 10%;padding: 5px 5px;"
                                                                    class="tac border-s-s-n-n border border-t-r-radius">
                                                                    {{ $board_item['taxableamount'] }}</td> --}}
                                                            @endif

                                                            @if ($data['pdf_permission']['product_detailed_gst_visible'] == 1)
                                                                <td style="width: 10%;padding: 5px 5px;"
                                                                    class="tac border-s-n-n-n border">
                                                                    {{ $board_item['igst_per'] }}</td>
                                                            @endif

                                                            @if (
                                                                $data['pdf_permission']['product_detailed_discount_visible'] == 1 ||
                                                                    $data['pdf_permission']['product_detailed_gst_visible'] == 1 ||
                                                                    $data['pdf_permission']['product_detailed_rate_total_visible'] == 1)
                                                                <td style="width: 10%;padding: 5px 5px;"
                                                                    class="tac border-s-s-n-n border border-t-r-radius">
                                                                    {{ numCommaFormat($board_item['net_amount']) }}
                                                                </td>
                                                            @endif

                                                        </tr>
                                                    @else
                                                        <tr>
                                                            @if ($board['board_name'] == null)
                                                                <td style="width: 25%;padding: 2px 2px;vertical-align: middle;"
                                                                    class="border-n-n-n-s border tac">
                                                                    <img src="{{ getSpaceFilePath($board_item['addons_image']) }}"
                                                                        style="object-fit: contain;" width="auto"
                                                                        height="40px">
                                                                </td>
                                                            @endif

                                                            <td style="width: 25%;padding: 5px 5px;" class="">
                                                                @if ($board_item['is_appendix'] != 0)
                                                                    <span
                                                                        class="appendixmark">#{{ $board_item['is_appendix'] }}</span>
                                                                @endif
                                                                {{ $board_item['itemname'] }}
                                                            </td>


                                                            {{-- @if ($data['pdf_permission']['product_detailed_discount_visible'] == 1 || $data['pdf_permission']['product_detailed_gst_visible'] == 1 || $data['pdf_permission']['product_detailed_rate_total_visible'] == 1) --}}
                                                            {{-- @if ($data['pdf_permission']['product_detailed_rate_total_visible'] == 1) --}}
                                                            <td style="width: 10%;padding: 5px 5px;" class="tac">
                                                                {{ numCommaFormat($board_item['rate']) }}</td>
                                                            {{-- @endif --}}

                                                            @if (
                                                                $data['pdf_permission']['product_detailed_rate_total_visible'] != 1 &&
                                                                    $data['pdf_permission']['product_detailed_discount_visible'] != 1 &&
                                                                    $data['pdf_permission']['product_detailed_gst_visible'] != 1)
                                                                <td style="width: 5%;padding: 5px 5px;"
                                                                    class="tac border-n-s-n-n border">
                                                                    {{ $board_item['qty'] }}</td>
                                                            @else
                                                                <td style="width: 5%;padding: 5px 5px;"
                                                                    class="tac "> {{ $board_item['qty'] }}</td>
                                                            @endif

                                                            @if ($data['pdf_permission']['product_detailed_discount_visible'] == 1)
                                                                <td style="width: 10%;padding: 5px 5px;"
                                                                    class="tac ">{{ $board_item['discper'] }}</td>
                                                            @endif

                                                            @if (
                                                                $data['pdf_permission']['product_detailed_discount_visible'] == 1 ||
                                                                    $data['pdf_permission']['product_detailed_gst_visible'] == 1)
                                                                <td style="width: 10%;padding: 5px 5px;"
                                                                    class="tac ">{{ $board_item['taxableamount'] }}
                                                                </td>
                                                                {{-- @elseif (
                                                                $data['pdf_permission']['product_detailed_discount_visible'] == 0 &&
                                                                    $data['pdf_permission']['product_detailed_gst_visible'] == 1)
                                                                <td style="width: 10%;padding: 5px 5px;"
                                                                    class="tac ">{{ $board_item['taxableamount'] }}
                                                                </td>
                                                            @elseif (
                                                                $data['pdf_permission']['product_detailed_discount_visible'] == 1 &&
                                                                    $data['pdf_permission']['product_detailed_gst_visible'] == 0)
                                                                <td style="width: 10%;padding: 5px 5px;"
                                                                    class="tac border-n-s-n-n border">
                                                                    {{ $board_item['taxableamount'] }}</td> --}}
                                                            @endif

                                                            @if ($data['pdf_permission']['product_detailed_gst_visible'] == 1)
                                                                <td style="width: 10%;padding: 5px 5px;"
                                                                    class="tac">
                                                                    {{ $board_item['igst_per'] }}</td>
                                                            @endif

                                                            @if (
                                                                $data['pdf_permission']['product_detailed_rate_total_visible'] == 1 ||
                                                                    $data['pdf_permission']['product_detailed_discount_visible'] == 1 ||
                                                                    $data['pdf_permission']['product_detailed_gst_visible'] == 1)
                                                                <td style="width: 10%;padding: 5px 5px;"
                                                                    class="tac border-n-s-n-n border">
                                                                    {{ numCommaFormat($board_item['net_amount']) }}
                                                                </td>
                                                            @endif
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <table
                                            style="border-collapse: separate; border-spacing: 0;width:100%;font-size: 14px">
                                            <tbody>
                                                <tr style="background-color:#f2f2f2;">
                                                    {{-- @if ($data['pdf_permission']['product_detailed_rate_total_visible'] == 1 || $data['pdf_permission']['product_detailed_discount_visible'] == 1 || $data['pdf_permission']['product_detailed_gst_visible'] == 1) --}}
                                                    {{-- @if ($data['pdf_permission']['product_detailed_rate_total_visible'] == 1) --}}
                                                    @if ($board['board_name'] == null)
                                                        <td style="padding: 7px 0px 7px 15px;" colspan="4"
                                                            class="border border-s-n-s-s border-b-l-radius">Room Addons
                                                        </td>
                                                    @else
                                                        <td style="padding: 7px 0px 7px 15px;" colspan="4"
                                                            class="border border-s-n-s-s border-b-l-radius">
                                                            {{ ucfirst(trans($board['board_name'])) }}</td>
                                                    @endif
                                                    <td style="padding: 7px 15px 7px 0px;text-align: right !important;"
                                                        colspan="4"
                                                        class="border border-s-s-s-n border-b-r-radius">Panel Price:
                                                        {{ numCommaFormat($board['board_price']) }}/- </td>
                                                    {{-- @else
                                                        @if ($board['board_name'] == null)
                                                            <td style="padding: 7px 0px 7px 15px;border: 1px solid #000"
                                                                colspan="4"
                                                                class="border-b-l-radius border-b-r-radius">Room Addons
                                                            </td>
                                                        @else
                                                            <td style="padding: 7px 0px 7px 15px;border: 1px solid #000"
                                                                colspan="4"
                                                                class="border-b-l-radius border-b-r-radius">
                                                                {{ ucfirst(trans($board['board_name'])) }}</td>
                                                        @endif
                                                        <td style="padding: 7px 15px 7px 0px;text-align: right !important; display: none;;"colspan="4"
                                                            class="border border-s-s-s-n border-b-r-radius">Panel
                                                            Price: {{ numCommaFormat($board['board_price']) }}/- </td>
                                                    @endif --}}
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div style="padding: 0px;margin: 0px;margin-top: 20px">
                            <table style="border-collapse: separate; border-spacing: 0;font-size: 14px">
                                <tr>
                                    <td colspan="4" style="padding:5px 0px 5px 20px;border-top: 1px solid #000;">
                                        {{ ucfirst(trans($room_value['room_name'])) }}</td>
                                    {{-- @if ($data['pdf_permission']['product_detailed_rate_total_visible'] == 1 || $data['pdf_permission']['product_detailed_discount_visible'] == 1 || $data['pdf_permission']['product_detailed_gst_visible'] == 1) --}}
                                    {{-- @if ($data['pdf_permission']['product_detailed_rate_total_visible'] == 1) --}}
                                    <td colspan="4"
                                        style="text-align:right; padding: 5px 20px 5px 0px;border-top: 1px solid #000;">
                                        Sub Total: Rs. {{ numCommaFormat($room_value['room_amount']) }}</td>
                                    {{-- @endif --}}
                                </tr>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- 6 page --}}
    @if ($data['pdf_permission']['wlt_and_others_detailed_summary_visible'] != 0)
        <div style="margin: 0px !important; padding: 0px !important;page-break-before: auto;">
            <div class="page-header header-page-break">
                <div
                    style="background-color: black;padding-top: 20px;padding-bottom: 20px;font-family: sans-serif;height:15%">
                    <table>
                        <tr>
                            <td style="width: 70%" class="pl50">
                                <h1 style="color: white;font-size: 35px;">Whitelion and
                                    Other
                                    <br>Products Summary
                                </h1>
                            </td>
                            <td style="width: 30%;text-align: right;vertical-align: bottom;" class="pr50"><img
                                    style="height: 60px;"
                                    src="https://erp.whitelion.in/assets/images/quotation_pdf/logo.svg">
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="page-body">
                @if (count($data['whitelion_product_summary']['whitelion_items']) > 0)
                    <h5
                        style="text-align: center;margin: 0px;padding: 5px 20px;border: 1px solid black;border-radius: 25px;margin-left: 12px;background-color: black;width: 14%;color: white;text-align: center;font-size: 24px;font-weight: bold;position: relative;">
                        Whitelion</h5>

                    <div
                        style="border: 1px solid gray;border-radius: 8px;width: auto;margin-top: -18px;padding: 28px 12px 12px;">
                        <table style="width: 100%;font-size: 14px;border-collapse: unset;border-spacing: 0px;">
                            <thead class="report-header " style="width: 100%;">
                                <tr style="text-align: left;font-size: 14px;" class="spacing">
                                    <td class="txtbold tac" style="padding: 0px 2px;width: 9%;">Sr No.</td>
                                    <td class="txtbold" style="padding: 0px 2px;width: 40%">Product Name</td>

                                    {{-- @if ($data['pdf_permission']['wlt_and_others_detailed_rate_total_visible'] == 1 || $data['pdf_permission']['wlt_and_others_detailed_discount_visible'] == 1 || $data['pdf_permission']['wlt_and_others_detailed_gst_visible'] == 1) --}}
                                    {{-- @if ($data['pdf_permission']['wlt_and_others_detailed_rate_total_visible'] == 1) --}}
                                    <td class="txtbold tac" style="padding: 0px 2px;width: 10%">Rate(Rs)</td>
                                    {{-- @endif --}}

                                    <td class="txtbold tac" style="padding: 0px 2px;width: 6%;">Qty</td>

                                    @if ($data['pdf_permission']['wlt_and_others_detailed_discount_visible'] == 1)
                                        <td class="txtbold tac" style="padding: 0px 2px;width: 10%;">Dis %</td>
                                    @endif

                                    @if (
                                        $data['pdf_permission']['wlt_and_others_detailed_discount_visible'] == 1 ||
                                            $data['pdf_permission']['wlt_and_others_detailed_gst_visible'] == 1)
                                        <td class="txtbold tac" style="padding: 0px 2px;width: 10%">Tax Value</td>
                                    @endif

                                    @if ($data['pdf_permission']['wlt_and_others_detailed_gst_visible'] == 1)
                                        <td class="txtbold tac" style="padding: 0px 2px;width: 10%">Gst %</td>
                                    @endif

                                    {{-- @if ($data['pdf_permission']['wlt_and_others_detailed_rate_total_visible'] == 1 || $data['pdf_permission']['wlt_and_others_detailed_discount_visible'] == 1 || $data['pdf_permission']['wlt_and_others_detailed_gst_visible'] == 1) --}}
                                    <td class="txtbold tac" style="padding: 0px 2px;width: 15%;">Total(Rs.)</td>
                                    {{-- @endif --}}
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data['whitelion_product_summary']['whitelion_items'] as $whitelion_company_item_key => $whitelion_company_item_value)
                                    <tr style="page-break-inside: avoid;">
                                        @if ($whitelion_company_item_value['subgroupname'] != '')
                                            <td class="txtbold" colspan="2"
                                                style="font-size: 12px;color: #878787;padding: 10px 0px 10px 10px;height: 5px;width:10%;">
                                                {{ $whitelion_company_item_value['subgroupname'] }}
                                            </td>
                                        @else
                                            <td style="height: 5px"></td>
                                        @endif
                                    </tr>
                                    <tr style="text-align: left;font-size: 14px !important;page-break-inside: avoid;">
                                        <td class="bdcoll txtbold border-top border-bottom border-left tac pr2 pl2 pt6 pb6"
                                            style="border-top-left-radius: 7px;border-bottom-left-radius: 7px;">
                                            {{ $whitelion_company_item_key + 1 }}</td>
                                        <td class="bdcoll txtbold border-top border-bottom pr2 pl2 pt6 pb6">
                                            @if ($whitelion_company_item_value['is_appendix'] != 0)
                                                <span
                                                    class="appendixmark">#{{ $whitelion_company_item_value['is_appendix'] }}</span>
                                            @endif
                                            {{ $whitelion_company_item_value['itemname'] }}</br><span
                                                style="font-size: 12px;color: #878787">({{ $whitelion_company_item_value['itemsubgroupname'] }})</span>
                                        </td>

                                        {{-- @if ($data['pdf_permission']['wlt_and_others_detailed_rate_total_visible'] == 1 || $data['pdf_permission']['wlt_and_others_detailed_discount_visible'] == 1 || $data['pdf_permission']['wlt_and_others_detailed_gst_visible'] == 1) --}}
                                        {{-- @if ($data['pdf_permission']['wlt_and_others_detailed_rate_total_visible'] == 1) --}}
                                        <td class="bdcoll txtbold border-top border-bottom tac pr2 pl2 pt6 pb6">
                                            {{ numCommaFormat(round($whitelion_company_item_value['rate'])) }}</td>
                                        {{-- @endif --}}

                                        @if (
                                            $data['pdf_permission']['wlt_and_others_detailed_rate_total_visible'] != 1 &&
                                                $data['pdf_permission']['wlt_and_others_detailed_discount_visible'] != 1 &&
                                                $data['pdf_permission']['wlt_and_others_detailed_gst_visible'] != 1)
                                            <td class="bdcoll tac txtbold border-top border-bottom tac pr2 pl2 pt6 pb6 border-right"
                                                style="border-top-right-radius: 7px;border-bottom-right-radius: 7px;">
                                                {{ $whitelion_company_item_value['whitelion_qty'] }}</td>
                                        @else
                                            <td class="bdcoll txtbold border-top border-bottom tac pr2 pl2 pt6 pb6">
                                                {{ $whitelion_company_item_value['whitelion_qty'] }}</td>
                                        @endif


                                        @if ($data['pdf_permission']['wlt_and_others_detailed_discount_visible'] == 1)
                                            <td class="bdcoll txtbold border-top border-bottom tac  pl2 pt6 pb6">
                                                {{ $whitelion_company_item_value['discper'] }}</td>
                                        @endif

                                        @if (
                                            $data['pdf_permission']['wlt_and_others_detailed_discount_visible'] == 1 ||
                                                $data['pdf_permission']['wlt_and_others_detailed_gst_visible'] == 1)
                                            <td class="bdcoll txtbold border-top border-bottom tac pr2 pl2 pt6 pb6">
                                                {{ numCommaFormat(round($whitelion_company_item_value['whitelion_grossamount'])) }}
                                            </td>
                                            {{-- @elseif (
                                            $data['pdf_permission']['wlt_and_others_detailed_discount_visible'] == 0 &&
                                                $data['pdf_permission']['wlt_and_others_detailed_gst_visible'] == 1)
                                            <td class="bdcoll txtbold border-top border-bottom tac pr2 pl2 pt6 pb6">
                                                {{ numCommaFormat(round($whitelion_company_item_value['whitelion_grossamount'])) }}
                                            </td>
                                        @elseif (
                                            $data['pdf_permission']['wlt_and_others_detailed_discount_visible'] == 1 &&
                                                $data['pdf_permission']['wlt_and_others_detailed_gst_visible'] == 0)
                                            <td class="bdcoll txtbold border-top border-bottom tac border-right pr2 pl2 pt6 pb6"
                                                style="border-top-right-radius: 7px;border-bottom-right-radius: 7px;">
                                                {{ numCommaFormat(round($whitelion_company_item_value['whitelion_grossamount'])) }}
                                            </td> --}}
                                        @endif

                                        @if ($data['pdf_permission']['wlt_and_others_detailed_gst_visible'] == 1)
                                            <td class="bdcoll txtbold border-top border-bottom tac pr2 pl2 pt6 pb6">
                                                {{ round($whitelion_company_item_value['igst_per']) }} </td>
                                        @endif

                                        {{-- @if ($data['pdf_permission']['wlt_and_others_detailed_rate_total_visible'] == 1 || $data['pdf_permission']['wlt_and_others_detailed_discount_visible'] == 1 || $data['pdf_permission']['wlt_and_others_detailed_gst_visible'] == 1) --}}
                                        <td class="bdcoll txtbold border-top border-bottom tac border-right pr2 pl2 pt6 pb6"
                                            style="border-top-right-radius: 7px;border-bottom-right-radius: 7px;">
                                            {{ numCommaFormat(round($whitelion_company_item_value['whitelion_net_amount'])) }}
                                        </td>
                                        {{-- @endif --}}
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <table class="page-break" style="padding-top: 0px !important;">
                            {{-- @if ($data['pdf_permission']['wlt_and_others_detailed_rate_total_visible'] == 1 || $data['pdf_permission']['wlt_and_others_detailed_discount_visible'] == 1 || $data['pdf_permission']['wlt_and_others_detailed_gst_visible'] == 1) --}}
                            {{-- @if ($data['pdf_permission']['wlt_and_others_detailed_rate_total_visible'] == 1) --}}
                            <tr class="txtbold" style="font-size: 14px;">
                                <td colspan="6" style="text-align: right;padding-right: 20px;">Total</td>
                                <td style="text-align: center;">
                                    {{ numCommaFormat($data['whitelion_product_summary']['whitelion_company_total_netamount']) }}
                                </td>
                            </tr>
                            {{-- @endif --}}
                        </table>
                    </div>
                @endif
                <h5
                    style="text-align: center;margin: 0px;padding: 5px 20px;border: 1px solid black;border-radius: 25px;margin-top: 20px;margin-left: 12px;background-color: black;width: 14%;color: white;text-align: center;font-size: 24px;font-weight: bold;position: relative;">
                    Others
                </h5>

                <div
                    style="border: 1px solid gray;border-radius: 8px;width: auto;margin-top: -18px;padding: 28px 12px 12px;">
                    <table style="width: 100%;font-size: 14px;border-collapse: unset;border-spacing: 0px;">
                        <thead class="report-header ">
                            <tr style="text-align: left;font-size: 14px;" class="spacing">
                                <td class="txtbold tac" style="padding: 0px 2px;width: 9%;">Sr No.</td>
                                <td class="txtbold" style="padding: 0px 2px;width: 40%">Product Name</td>

                                {{-- @if ($data['pdf_permission']['wlt_and_others_detailed_rate_total_visible'] == 1 || $data['pdf_permission']['wlt_and_others_detailed_discount_visible'] == 1 || $data['pdf_permission']['wlt_and_others_detailed_gst_visible'] == 1) --}}
                                {{-- @if ($data['pdf_permission']['wlt_and_others_detailed_rate_total_visible'] == 1) --}}
                                <td class="txtbold tac" style="padding: 0px 2px;width: 10%">Rate(Rs)</td>
                                {{-- @endif --}}

                                <td class="txtbold tac" style="padding: 0px 2px;width: 6%">Qty</td>

                                @if ($data['pdf_permission']['wlt_and_others_detailed_discount_visible'] == 1)
                                    <td class="txtbold tac" style="padding: 0px 2px;width: 10%">Dis %</td>
                                @endif

                                @if (
                                    $data['pdf_permission']['wlt_and_others_detailed_discount_visible'] == 1 ||
                                        $data['pdf_permission']['wlt_and_others_detailed_gst_visible'] == 1)
                                    <td class="txtbold tac" style="padding: 0px 2px;width: 10%">Tax Value</td>
                                @endif

                                @if ($data['pdf_permission']['wlt_and_others_detailed_gst_visible'] == 1)
                                    <td class="txtbold tac" style="padding: 0px 2px;width: 10%">Gst %</td>
                                @endif

                                {{-- @if ($data['pdf_permission']['wlt_and_others_detailed_rate_total_visible'] == 1 || $data['pdf_permission']['wlt_and_others_detailed_discount_visible'] == 1 || $data['pdf_permission']['wlt_and_others_detailed_gst_visible'] == 1) --}}
                                <td class="txtbold tac" style="padding: 0px 2px;width: 15%">Total(Rs.)</td>
                                {{-- @endif --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data['others_product_summary']['others_company_item'] as $others_company_item_key => $others_company_item_value)
                                <tr style="page-break-inside: avoid;">
                                    @if ($others_company_item_value['subgroupname'] != '')
                                        <td class="txtbold" colspan="2"
                                            style="font-size: 12px;color: #878787;padding: 10px 0px 10px 10px;height: 5px;width:10%;">
                                            {{ $others_company_item_value['subgroupname'] }}
                                        </td>
                                    @else
                                        <td style="height: 5px"></td>
                                    @endif
                                </tr>
                                <tr style="text-align: left;font-size: 14px !important;page-break-inside: avoid;">
                                    <td class="bdcoll txtbold border-top border-bottom border-left tac pr2 pl2 pt6 pb6"
                                        style="border-top-left-radius: 7px;border-bottom-left-radius: 7px;">
                                        {{ $others_company_item_key + 1 }}</td>
                                    <td class="bdcoll txtbold border-top border-bottom pr2 pl2 pt6 pb6">
                                        @if ($others_company_item_value['is_appendix'] != 0)
                                            <span
                                                class="appendixmark">#{{ $others_company_item_value['is_appendix'] }}</span>
                                        @endif
                                        {{ $others_company_item_value['itemname'] }} -
                                        {{ $others_company_item_value['itemsubgroupname'] }}</br><span
                                            style="font-size: 12px;color: #878787">({{ $others_company_item_value['code'] }})</span>
                                    </td>

                                    {{-- @if ($data['pdf_permission']['wlt_and_others_detailed_discount_visible'] == 1 || $data['pdf_permission']['wlt_and_others_detailed_gst_visible'] == 1) --}}
                                    {{-- @if ($data['pdf_permission']['wlt_and_others_detailed_rate_total_visible'] == 1) --}}
                                    <td class="bdcoll txtbold border-top border-bottom tac pr2 pl2 pt6 pb6">
                                        {{ numCommaFormat(round($others_company_item_value['rate'])) }}</td>
                                    {{-- @endif --}}

                                    @if (
                                        $data['pdf_permission']['wlt_and_others_detailed_rate_total_visible'] != 1 &&
                                            $data['pdf_permission']['wlt_and_others_detailed_discount_visible'] != 1 &&
                                            $data['pdf_permission']['wlt_and_others_detailed_gst_visible'] != 1)
                                        <td class="bdcoll tac txtbold border-top border-bottom tac pr2 pl2 pt6 pb6 border-right"
                                            style="border-top-right-radius: 7px;border-bottom-right-radius: 7px;">
                                            {{ $others_company_item_value['others_qty'] }}</td>
                                    @else
                                        <td class="bdcoll txtbold border-top border-bottom tac pr2 pl2 pt6 pb6">
                                            {{ $others_company_item_value['others_qty'] }}</td>
                                    @endif


                                    @if ($data['pdf_permission']['wlt_and_others_detailed_discount_visible'] == 1)
                                        <td class="bdcoll txtbold border-top border-bottom tac  pl2 pt6 pb6">
                                            {{ $others_company_item_value['discper'] }}</td>
                                    @endif

                                    @if (
                                        $data['pdf_permission']['wlt_and_others_detailed_discount_visible'] == 1 ||
                                            $data['pdf_permission']['wlt_and_others_detailed_gst_visible'] == 1)
                                        <td class="bdcoll txtbold border-top border-bottom tac pr2 pl2 pt6 pb6">
                                            {{ numCommaFormat(round($others_company_item_value['others_grossamount'])) }}
                                        </td>
                                        {{-- @elseif (
                                        $data['pdf_permission']['wlt_and_others_detailed_discount_visible'] == 0 &&
                                            $data['pdf_permission']['wlt_and_others_detailed_gst_visible'] == 1)
                                        <td class="bdcoll txtbold border-top border-bottom tac pr2 pl2 pt6 pb6">
                                            {{ numCommaFormat(round($others_company_item_value['others_grossamount'])) }}
                                        </td>
                                    @elseif (
                                        $data['pdf_permission']['wlt_and_others_detailed_discount_visible'] == 1 &&
                                            $data['pdf_permission']['wlt_and_others_detailed_gst_visible'] == 0)
                                        <td class="bdcoll txtbold border-top border-bottom tac border-right pr2 pl2 pt6 pb6"
                                            style="border-top-right-radius: 7px;border-bottom-right-radius: 7px;">
                                            {{ numCommaFormat(round($others_company_item_value['others_grossamount'])) }}
                                        </td> --}}
                                    @endif

                                    @if ($data['pdf_permission']['wlt_and_others_detailed_gst_visible'] == 1)
                                        <td class="bdcoll txtbold border-top border-bottom tac pr2 pl2 pt6 pb6">
                                            {{ round($others_company_item_value['igst_per']) }} </td>
                                    @endif

                                    {{-- @if ($data['pdf_permission']['wlt_and_others_detailed_rate_total_visible'] == 1 || $data['pdf_permission']['wlt_and_others_detailed_discount_visible'] == 1 || $data['pdf_permission']['wlt_and_others_detailed_gst_visible'] == 1) --}}
                                    {{-- @if ($data['pdf_permission']['wlt_and_others_detailed_rate_total_visible'] == 1) --}}
                                    <td class="bdcoll txtbold border-top border-bottom tac border-right pr2 pl2 pt6 pb6"
                                        style="border-top-right-radius: 7px;border-bottom-right-radius: 7px;">
                                        {{ numCommaFormat(round($others_company_item_value['others_net_amount'])) }}
                                    </td>
                                    {{-- @endif --}}
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <table class="page-break" style="padding-top: 0px !important;">
                        {{-- @if ($data['pdf_permission']['wlt_and_others_detailed_rate_total_visible'] == 1 || $data['pdf_permission']['wlt_and_others_detailed_discount_visible'] == 1 || $data['pdf_permission']['wlt_and_others_detailed_gst_visible'] == 1) --}}
                        {{-- @if ($data['pdf_permission']['wlt_and_others_detailed_rate_total_visible'] == 1) --}}
                        <tr class="txtbold" style="font-size: 14px;">
                            <td colspan="6" style="text-align: right;padding-right: 20px;">Total</td>
                            <td style="text-align: center;">
                                {{ numCommaFormat($data['others_product_summary']['others_company_total_netamount']) }}
                            </td>
                        </tr>
                        {{-- @endif --}}
                    </table>
                </div>
            </div>
        </div>
    @endif
    {{-- NEW UPDATE --}}

    {{-- 7 page --}}
    <div class="page-break" style="margin: 0px !important; padding: 0px !important;">
        <div class="page-header">
            <div style="background-color: black;padding-top: 25px;font-family: sans-serif;height: 217px;">
                <table>
                    <tr>
                        <td style="position: relative;top: -40px;">
                            <h1 style="color: white;margin-left: 40px;font-size: 35px;position: relative;top: -60px;">
                                AirCare</h1>
                            <h4 style="color: white;margin-left: 40px;font-size: 15px;position: relative;top: -60px;">
                                Whitelion's <br> 7 years warranty</h4>
                        </td>
                        <td>
                            <img style="position: relative;left: 100px;bottom: 0px;width: 150px;height: 170px;"
                                src="https://erp.whitelion.in/assets/images/quotation_pdf/7.svg">
                            <div
                                style="color: white;color: white;transform: skewX(335deg);margin-left: 50px;bottom: 145px;left: 140px;position: relative;">
                                <div style="font-size: 25px;transform: skewX(25deg);">Years of</div>
                                <div style="font-size: 25px;transform: skewX(25deg);">hassle free</div>
                                <div style="font-size: 25px;transform: skewX(25deg);">customer</div>
                                <div style="font-size: 25px;transform: skewX(25deg);">service</div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="page-body">
            <table style="margin-bottom: 30px;">
                <tr>
                    <td>
                        <div
                            style="border: 1px solid gray;border-radius: 50px; font-size: 16px; width:90%; font-family: Arial;margin:20px 0px 0px 0px ;height: 80px;display: inline-block;bottom: 0px; position: relative;">
                            <div
                                style="background-color: #000 ;width: 30px;height: 30px;border-radius: 50%;float: left;margin: 9px 10px 0px 9px;padding: 15px 17px 17px 15px ;">
                                <img src="https://erp.whitelion.in/assets/images/quotation_pdf/callcenter.svg"
                                    alt="" height="50px" width="50px">
                            </div>
                            <span
                                style="font-size: 35px;font-weight: bold;margin-left: 10px;top: 10; position: relative;">70965
                                26279</span><br>
                            <span style="position: relative; top: 10px;margin-left: 10px;">support@whitelion.in</span>
                        </div>
                    </td>
                    <td>
                        <div style="display: inline-block;width: 100%;">
                            <table style="width: 100%">
                                <tr>
                                    <td rowspan="2" style="width: 20%;">
                                        <img src="https://erp.whitelion.in/assets/images/quotation_pdf/barcode.png"
                                            alt=""
                                            style="width: 100px; height: 100px;position: relative;left: -100px;">
                                    </td>
                                    <td style="width: 80%;">
                                        <span
                                            style="left: 15px; position: relative; font-size: 26px;font-weight: bold;">Scan
                                            QR Code</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 80%;">
                                        <span
                                            style="left: 15px; position: relative;font-size: 16px;margin-top: -10px;display: inline-block">To
                                            register your product online and know about warranty details,terms and
                                            conditions &amp; charges.</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
            <div style="text-align: justify;top: -15px; position: relative;">
                <span style="font-size: 17px; font-weight: 00;margin-bottom: 15px; display: block;">As a warranty we,
                    Whitelion System Pvt.Ltd. or the company, will replace/repair any part or parts <br>of the product
                    that has a manufacturing defect within a year of bill date without any charges. For<br>next 6 year a
                    Labour Service Charge per panel will apply according to policy.<br> </span>
                <span style="font-size: 17px; font-weight: 00;margin-bottom: 5px; display: block;">You can also write
                    to us at below address<br></span>
                <span style="font-size: 17px; font-weight: 00;">8th floor, Union Heights, B Wing, Maharana Pratap Road,
                    Piplod, Surat, Gujrat-395007.</span>
            </div>
            <div style="text-align: center; margin: 30px 0px 0px 0px;">
                <span style="font-size: 17px; font-weight: 0;">Steps to avail 7 years of warranty</span><br><br>
            </div>
            <div style="text-align: left; margin: 2% 0% 0% 35%;">
                <div class="warranty_img_div1">
                    <img src="https://erp.whitelion.in/assets/images/quotation_pdf/barcode.png" alt=""
                        class="warranty_img">
                </div>
                <div class="warranty_img_div2">
                    <img src="https://erp.whitelion.in/assets/images/quotation_pdf/form.png" alt=""
                        class="warranty_img">
                </div>
                <ul class="StepProgress">
                    <li class="StepProgress-item">
                        <span class="warranty_step">Scan below QR
                            Code</span>
                    </li>
                    <li class="StepProgress-item" style="margin-top: 20%">
                        <span class="warranty_step">Register your
                            product</span>
                    </li>
                    <li class="StepProgress-item" style="margin-top: 20%">
                        <span class="warranty_step">Enjoy hassle-free
                            warranty</span>
                    </li>
                </ul>
            </div>
            <div style="text-align: center; margin: 10% 0% 0% 0%;">
                <span style="font-size: 17px; font-weight: 0;">or visit us on</span><br><br>
                <div
                    style="border: 1px solid gray;border-radius: 25px; font-size: 16px;  margin: 15px;width: 270px; font-family: Arial;text-align: center;margin-left: 32%;padding: 8px 5px 8px 10px;">
                    <a href="https://www.whitelion.in/productRegistration" target="_blank">
                        <span>whitelion.in/productRegistration</span>
                        <img src="https://erp.whitelion.in/assets/images/quotation_pdf/link.svg" alt=""
                            style="width: 15px;margin-left:0px;">
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 8 page --}}
    <div class="page-break" style="margin: 0px !important; padding: 0px !important;">
        <div class="page-header">
            <div
                style="background-color: black;padding-top: 20px;padding-bottom: 20px;font-family: sans-serif;height:15%">
                <table>
                    <tr>
                        <td style="width: 70%" class="pl50">
                            <h1 style="color: white;font-size: 35px;">People Love Us </h1>
                            <h4 style="color: white;font-size: 15px;">Customer satisfaction is<br>what keep us driving
                            </h4>

                        </td>
                        <td style="width: 30%;text-align: right;vertical-align: bottom;" class="pr50"><img
                                style="height: 150px;bottom: -31px;position: relative;"
                                src="https://erp.whitelion.in/assets/images/quotation_pdf/star_hand.png">
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="page-body">
            <table>
                <tr>
                    <td style="width: 80%">
                        <h1>Hear it from customers</h1>
                        <span style="word-spacing: 2px;font-size: 17px;">how whitelion helped them automate their daily
                            <br>life with smart home solutions.</span>
                    </td>
                </tr>
            </table>
            <table style="margin-top:30px">
                <tr>
                    <td>
                        <div class="customer_div" style="">
                            <div style="">
                                <div class="customer_img">
                                    <img src="https://erp.whitelion.in/assets/images/quotation_pdf/kamlesh_patel.png"
                                        alt=""
                                        style="height: 100px;width:100px;top: -23px; position: relative;">
                                    <div class="customer_name" style="top: 30px !important;">
                                        <span
                                            style="font-size:18px ;font-weight: bold;margin: 0px 0px 0px 0px;">KAMLESH
                                            PATEL</span>
                                        <span style="font-size:14px ;margin: 0px 0px 0px 10px;">(Director - SAP
                                            India)</span>
                                        <div class="customer_city">
                                            <span>Mumbai</span>
                                        </div>
                                        <div class="customer_content">
                                            Whitelion - Switch to Touch, the best experience of touch as an end user. In
                                            today's
                                            world where customer centricity is prime important, Whitelion team sets the
                                            perfect
                                            example of Customer Touch. Very Happy on the decision of moving ahead with
                                            Whitelion. As
                                            a happy customer I give full marks to: <br>1. Customer Experience &
                                            Service<br>2. Product Easy Installation<br>3. Product Ease of Use
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="customer_div" style="margin-top: 25px;">
                            <div style="">
                                <div class="customer_img">
                                    <img src="https://erp.whitelion.in/assets/images/quotation_pdf/sahil.png"
                                        alt=""
                                        style="height: 100px;width:100px;top: 5px; position: relative;">
                                </div>
                                <div class="customer_name" style="top: 25px !important;">
                                    <span
                                        style="font-size:18px ;font-weight: bold;margin: 0px 0px 0px 0px;">SAHIL</span>
                                    <span style="font-size:14px ;margin: 0px 0px 0px 10px;">(Euro india pvt
                                        ltd.)</span>
                                    <div class="customer_city">
                                        <span>Surat</span>
                                    </div>
                                    <div class="customer_content">I got my hands on Whitelion
                                        switch and now after using it I just cannot think of a reason to not purchase
                                        this. Not
                                        only it is easy to use but it indeed has made life much easier with its
                                        innovative tech
                                        specs. They have excellent customer service. Technicians would guide you
                                        thoroughly with
                                        installation and it's usage. This luxury product should be on your purchase list
                                        if you
                                        are looking for ways to make your home or work space a smart one.</div>
                                </div>
                            </div>

                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="customer_div" style="margin-top: 25px;">
                            <div style="margin-top: 20px;">
                                <div class="customer_img">
                                    <img src="https://erp.whitelion.in/assets/images/quotation_pdf/manish_choksi.png"
                                        alt="" style="height: 100px;width:100px;">
                                    <div class="customer_name" style="top: -30px !important;">
                                        <span style="font-size:18px ;font-weight: bold;margin: 0px 0px 0px 0px;">MANISH
                                            CHOKSI</span>
                                        <div class="customer_city">
                                            <span>Surat</span>
                                        </div>
                                        <div class="customer_content">Living in a dynamic world we
                                            come across such innovation in switches which are user friendly and
                                            ergonomically
                                            convenient. Whitelion also having colour options to suit every interior
                                            style.</div>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- 9 page --}}
    @if ($data['appendix_count'] != 0)
        <div class="page-break" style="margin: 0px !important; padding: 0px !important;">
            <div class="page-header">
                <div style="background-color: black;padding-top: 25px;font-family: sans-serif;height: 200px;">
                    <table>
                        <tr>
                            <td style="position: relative;top: -40px;">
                                <h1 style="color: white;margin-left: 40px;font-size: 35px;position: relative;">
                                    Precautions & <br>Reccomendations</h1>
                            </td>
                            <td>
                                <img src="https://erp.whitelion.in/assets/images/quotation_pdf/page_9_header.svg"
                                    alt=""
                                    style="height: 100%; width: 100%;left:0%;top: -7px;position: relative;z-index: -99;">
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="page-body">
                <div
                    style="page-break-inside: avoid;page-break-after: auto;border: 1px solid #90c29d;border-radius: 10px;margin-bottom: 30px;">
                    <div style="background-color: #90c29d;border-top-right-radius: 7px;border-top-left-radius: 7px;">
                        <table>
                            <tr>
                                <td style="width: 50%">
                                    <h2 style="margin:10px 25px;">Wiring Recommendations</h2>
                                </td>
                                <td style="width: 50%">
                                    <img style="position: relative;left: 80%;width: 35px;height:35px;padding: 10px 0px;"
                                        src="https://erp.whitelion.in/assets/images/quotation_pdf/wiring.svg">
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="additional_info_ckeditor" style="height: auto;margin: 10px;">
                        @foreach ($data['lstappendix'] as $lstappendix_key => $objappendix)
                            <div class="tal"
                                style="border: 1px solid red;padding: 5px;border-radius: 5px; font-size: 12px;margin: 8px 13px 0px 13px;">
                                <span style="color: red;font-weight: bold;">#{{ $objappendix['is_appendix'] }}</span>
                                <br>
                                {!! $objappendix['additional_info'] !!}
                            </div>
                        @endforeach

                        <div style="margin: 10px;font-size: 17px;">
                            <span>
                                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor
                                incididunt
                                ut
                                labore et dolore magna aliqua. <br>Ut enim ad minim veniam, quis nostrud exercitation
                                ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in
                                reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
                            </span>
                        </div>
                    </div>
                </div>

                <div style="page-break-inside: avoid;page-break-after: auto;">
                    <div style="border: 1px solid #96c4de;border-radius: 10px;margin-bottom: 30px;">
                        <div
                            style="background-color: #96c4de;border-top-right-radius: 7px;border-top-left-radius: 7px;">
                            <table>
                                <tr>
                                    <td style="width: 50%">
                                        <h2 style="margin:10px 25px;">Network Reccomendations</h2>
                                    </td>
                                    <td style="width: 50%">
                                        <img style="position: relative;left: 80%;width: 35px;height:35px;padding: 10px 0px;"
                                            src="https://erp.whitelion.in/assets/images/quotation_pdf/network.svg">
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div style="height: auto;margin: 10px;">
                            <div style="margin: 10px;font-size: 17px;">
                                <span>
                                    Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor
                                    incididunt
                                    ut
                                    labore et dolore magna aliqua. <br><br>Ut enim ad minim veniam, quis nostrud
                                    exercitation
                                    ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in
                                    reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="page-break-inside: avoid;page-break-after: auto;">
                    <div style="border: 1px solid #ef9796;border-radius: 10px;margin-bottom: 30px;">
                        <div
                            style="background-color: #ef9796;border-top-right-radius: 7px;border-top-left-radius: 7px;">
                            <table>
                                <tr>
                                    <td style="width: 50%">
                                        <h2 style="margin:10px 25px;">Load Reccomendations</h2>
                                    </td>
                                    <td style="width: 50%">
                                        <img style="position: relative;left: 80%;width: 35px;height:35px;padding: 10px 0px;"
                                            src="https://erp.whitelion.in/assets/images/quotation_pdf/load.svg">
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div style="height: auto;margin: 10px;">
                            <div style="margin: 10px;font-size: 17px;">
                                <span>
                                    Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor
                                    incididunt
                                    ut
                                    labore et dolore magna aliqua. <br><br>Ut enim ad minim veniam, quis nostrud
                                    exercitation
                                    ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in
                                    reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- 10 page --}}
    {{-- <div class="page-break" style="margin: 0px !important; padding: 0px !important;min-height: 100%;">
        <div class="page-header">
            <div style="background-color: black;padding-top: 25px;font-family: sans-serif;height: 170px;">
                <table>
                    <tr>
                        <td style="position: relative;padding: 20px 0px 30px 0px">
                            <h1 style="color: white;margin-left: 40px;font-size: 35px;position: relative;top: -30px;">
                                Detailed Technical<br>Specification</h1>
                            <h4 style="color: white;margin-left: 40px;font-size: 15px;position: relative;top: -30px;">
                                for
                                whitelion products</h4>
                        </td>
                        <td>
                            <img src="https://erp.whitelion.in/assets/images/quotation_pdf/whitelionlogo.svg" alt="" style="height: 150px; width: 150px;left: 190px;position: relative;">
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="page-body">
            <table style="border-collapse: collapse;">
                <thead style="background-color: #dfdfdf;">
                    <tr>
                        <th style="padding: 15px 0px;width: 5%" class="border-top-left-radius">No.</th>
                        <th style="width: 25%;">Function</th>
                        <th style="width: 20%;">Sub-Function</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 40%;" class="border-top-right-radius">Remark</th>
                    </tr>
                </thead>
                <tbody style="font-size: 12px;">
                    <tr>
                        <td class="bb bl br bt p15">1.</td>
                        <td class="bb bl br bt p15">Communication Method</td>
                        <td class="bb bl br bt p15">Wireless</td>
                        <td class="bb bl br bt p15">Yes</td>
                        <td class="bb bl br bt p15">So it is retrofit for every customer, No Need to do rewiring.</td>
                    </tr>
                    <tr>
                        <td class="bb bl br bt p15">2.</td>
                        <td class="bb bl br bt p15">Communication Protocol</td>
                        <td class="bb bl br bt p15">Wireless Mode</td>
                        <td class="bb bl br bt p15">Wi-Fi</td>
                        <td class="bb bl br bt p15">IEEE 802.11 b/g/n compliant - speediest communication strategy .
                        </td>
                    </tr>
                    <tr>
                        <td class="bb bl br bt p15">3.</td>
                        <td class="bb bl br bt p15">Communication Media</td>
                        <td class="bb bl br bt p15">Wireless - 2.4 Ghz</td>
                        <td class="bb bl br bt p15">Yes</td>
                        <td class="bb bl br bt p15">IEEE 802.11 b/g/n compliant</td>
                    </tr>
                    <tr>
                        <td class="bb bl br bt p15">4.</td>
                        <td class="bb bl br bt p15">Communication Media</td>
                        <td class="bb bl br bt p15">Wireless - 2.4 Ghz</td>
                        <td class="bb bl br bt p15">Yes</td>
                        <td class="bb bl br bt p15">Separate wifi chip is connected with display to make them smart
                        </td>
                    </tr>
                    <tr>
                        <td class="bb bl br bt p15">5.</td>
                        <td class="bb bl br bt p15">Network Topology</td>
                        <td class="bb bl br bt p15">P2P Connection</td>
                        <td class="bb bl br bt p15">Yes</td>
                        <td class="bb bl br bt p15">Failure chances and disconnect chances are less than 1%</td>
                    </tr>
                    <tr>
                        <td class="bb bl br bt p15">6.</td>
                        <td class="bb bl br bt p15">Network Architecture</td>
                        <td class="bb bl br bt p15">Tree Structure</td>
                        <td class="bb bl br bt p15">Yes</td>
                        <td class="bb bl br bt p15">No separate centralized controller or hub required such as each
                            smart switch has its own controller for local processing</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div> --}}

    {{-- 11 page --}}
    <div class="page-break" style="margin: 0px !important; padding: 0px !important;">
        <div class="header-page-break">
            <div style="background-color: black;padding-top: 25px;font-family: sans-serif;">
                <img src="https://erp.whitelion.in/assets/images/quotation_pdf/whitelion-full-size-logo.svg"
                    alt="">
            </div>
        </div>
        <div class="page-body">
            <div style="margin: 40px;">
                <div style="font-size: 26px">Do More Live More With<br>Whitelion Smart Home Automation...</div>
                <div style="position: relative;bottom: -500px;width: 48%;display: inline-block;">
                    <h3>Whitelion Systems Pvt Ltd.</h3>
                    <span style="font-size: 13px;line-height: 1.5em;">8<sup>th</sup> Floor, B Wing Union
                        Heights, Opp. Rahul Raj Mall,<br>
                        Next to Lalbhai Contractor stadium, Piplod,<br>
                        Surat-395007, Gujarat, INDIA</span>
                </div>
                <div style="width: 48%;display: inline-block;bottom: -515px;position: relative;left: 100px;">
                    <div>
                        <table>
                            <tr>
                                <td style="width: 10%">
                                    <div class="container">
                                        <img src="https://erp.whitelion.in/assets/images/quotation_pdf/contact_footer.svg"
                                            alt="" class="img1">
                                    </div>
                                </td>
                                <td style="text-align: left">
                                    <span style="bottom: 0px;position: relative;left: 10px;">+91 74050 29883</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div>
                        <table>
                            <tr>
                                <td style="width: 10%">
                                    <div class="container">
                                        <img src="https://erp.whitelion.in/assets/images/quotation_pdf/web_footer.svg"
                                            alt="" class="img2">
                                    </div>
                                </td>
                                <td style="text-align: left">
                                    <span style="bottom: 0px;position: relative;left: 10px;">www.whitelion.in</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div>
                        <table>
                            <tr>
                                <td style="width: 10%">
                                    <div class="container">
                                        <img src="https://erp.whitelion.in/assets/images/quotation_pdf/gmail_footer.svg"
                                            alt="" class="img3">
                                    </div>
                                </td>
                                <td style="text-align: left">
                                    <span style="bottom: 0px;position: relative;left: 10px;">info@whitelion.in</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div style="border: 1px solid #b9b9b9;bottom: -550px;position: relative;"></div>

                <div style="bottom: -574px;position: relative;">
                    <table>
                        <tr>
                            <td style="width: 15%; text-align: left;">
                                <span style="margin-right: 10px;font-size: 12px;">We are social</span>
                            </td>
                            <td class="icons" style="text-align: left;">
                                <a style="width: 15px;" href="https://www.facebook.com/whiteliongrp/"
                                    target="_blank"><img
                                        src="https://erp.whitelion.in/assets/images/quotation_pdf/facebook-svgrepo-com.svg"
                                        style="width: 20px;margin-right:12px;margin-top:10px;position: relative;"></a>
                                <a style="width: 15px;" href="https://www.instagram.com/whitelion.in/"
                                    target="_blank"><img
                                        src="https://erp.whitelion.in/assets/images/quotation_pdf/instagram_footer.svg"
                                        style="width: 15px;margin-right:12px;margin-bottom: 5px; position: relative;"></a>
                                <a style="width: 15px;"
                                    href="https://www.youtube.com/channel/UC0bKSHhDxxIybUJXyrQ4pMQ/"
                                    target="_blank"><img
                                        src="https://erp.whitelion.in/assets/images/quotation_pdf/black-and-white-youtube-icon.svg"
                                        style="width: 20px;margin-right:12px;margin-bottom: 4px;position: relative;"></a>
                                <a style="width: 15px;" href="https://www.linkedin.com/company/whitelion-in/"
                                    target="_blank"><img
                                        src="https://erp.whitelion.in/assets/images/quotation_pdf/linkedin-square-icon.svg"
                                        style="width: 20px;margin-right:12px;position: relative;"></a>
                                <a style="width: 15px;"
                                    href="https://twitter.com/i/flow/login?redirect_after_login=%2Fwhitelion_in"
                                    target="_blank"><img
                                        src="https://erp.whitelion.in/assets/images/quotation_pdf/twitter_footer.svg"
                                        style="width: 15px;margin-right:12px;margin-bottom: 4px;position: relative;"></a>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

<script type="text/php">
    if (isset($pdf)) {
        $x = 525;
        $y = 825;
        $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $font = null;
        $size = 10;
        $color = array(0,0,0,1);
        $word_space = 0.0;  //  default
        $char_space = 0.0;  //  default
        $angle = 0.0;   //  default
        $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
    }
</script>

</html>
