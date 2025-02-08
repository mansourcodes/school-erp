<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @include(backpack_view('inc.head'))

</head>

<body>
    <div id="app">

        <div style="position: fixed; top:1rem; right:1rem;z-index: 9999999999;" class="text-center  noPrint">
            <div class="text-center btn btn-primary noPrint" onclick="printPage()">
                <i class="la la-print"></i> طباعة
            </div>
        </div>

        @yield('content')


        <style>
            @page {
                size: auto;
            }

            @media print {
                .noPrint {
                    display: none;
                }
            }

            @media print {
                .new-page {
                    page-break-after: always;
                }
            }

            body {
                background-color: #fff !important;
                -webkit-print-color-adjust: exact !important;
            }

            .app {
                padding: 20px;
            }

            .page {
                position: relative;
            }

            table.table {
                border-collapse: collapse;
                margin: 1%;
                width: 98%;
            }

            table.table td,
            table.table th {
                padding: 5px;
                border: 1px solid black !important;
            }

            table.table th {
                background-color: #ccc !important;
            }

            table.table.table-no-border td,
            table.table.table-no-border th {
                border: 0 solid black !important;
                background-color: transparent !important;

            }


            table.table.table-ziped td,
            table.table.table-ziped th {
                padding: 2px 15px;
                font-size: 14px;
            }

            h1.title {
                /* position: absolute;
        top: 60px;
        left: 50%;
        margin-left: -200px;
        width: 400px; */
                font-size: 2rem;

            }
        </style>

        <script>
            // window.print();

            function printPage() {
                window.print();
                console.log('printpage');

            }
        </script>

        @yield('script')
    </div>
</body>

</html>
