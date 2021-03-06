@extends('layouts.default')

@section('content')
    <div class="box box-primary" style="padding: 0.01em 16px;">
        <h2>Profit By Bot</h2>

        <div class="w3-row">
            <a href="javascript:void(0)" onclick="openTab(event, 'both');">
                <div  id ="tab_both"  class="w3-third tablink w3-bottombar w3-hover-light-grey w3-padding w3-border-red">Both</div>
            </a>
            <a href="javascript:void(0)" onclick="openTab(event, 'long');">
                <div class="w3-third tablink w3-bottombar w3-hover-light-grey w3-padding">Long</div>
            </a>
            <a href="javascript:void(0)" onclick="openTab(event, 'short');">
                <div class="w3-third tablink w3-bottombar w3-hover-light-grey w3-padding">Short</div>
            </a>
        </div>

        <div id="both" class="tab" style="display:block">
            <div class="form-group" style="margin-top: 15px;">
                <div class="form-group col-md-2">
                    <label>Base</label>
                    <select id="base_both" class="select2 base" style="width: 150px;">
                        @foreach($both as $item)
                            <option value="{{ $item->base }}">{{ $item->base }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Accounts</label>
                    <select id="account_both" class="select2 account" multiple="multiple" style="width: 300px;"></select>
                </div>
                <div class="form-group col-md-3">
                    <label>Type</label>
                    <select id="type_both" class="select2 type" style="width: 300px;">
                        <option value="All">All</option>
                        <option value="ACTIVE">Exclude Delete</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <div class="input-group">
                        <button type="button" class="btn btn-default form-control pull-right daterange" id="daterange_both">
                    <span>
                      <i class="fa fa-calendar"></i> Please select date range
                    </span>
                            <i class="fa fa-caret-down"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div id="chart_both" style="min-width: 310px; min-height: 400px; height: 100%; margin: 0 auto"></div>
            <div>
                <table id ="tbl_both_unique" class="table table-bordered table-striped table-hover-blue"></table>
            </div>
            <div class="mt-4">
                <table id ="tbl_both" class="table table-bordered table-striped table-hover-blue"></table>
            </div>
        </div>

        <div id="long" class="tab" style="display:none">
            <div class="form-group" style="margin-top: 15px;">
                <div class="form-group" style="margin-top: 15px;">
                    <div class="form-group col-md-2">
                        <label>Base</label>
                        <select id="base_long" class="select2 base" style="width: 150px;">
                            @foreach($both as $item)
                                <option value="{{ $item->base }}">{{ $item->base }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Accounts</label>
                        <select id="account_long" class="select2 account" multiple="multiple" style="width: 300px;"></select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Type</label>
                        <select id="type_long" class="select2 type" style="width: 300px;">
                            <option value="All">All</option>
                            <option value="ACTIVE">Exclude Delete</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <div class="input-group">
                            <button type="button" class="btn btn-default form-control pull-right daterange" id="daterange_long">
                        <span>
                          <i class="fa fa-calendar"></i> Please select date range
                        </span>
                                <i class="fa fa-caret-down"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="chart_long" style="min-width: 310px; height: 400px; margin: 0 auto"></div>

            <div style="margin-top: 50px;">
                <table id ="tbl_long_unique" class="table table-bordered table-striped table-hover-blue"></table>
            </div>
            <div>
                <table id ="tbl_long" class="table table-bordered table-striped table-hover-blue"></table>
            </div>
        </div>

        <div id="short" class="tab" style="display:none">
            <div class="form-group" style="margin-top: 15px;">
                <div class="form-group" style="margin-top: 15px;">
                    <div class="form-group col-md-2">
                        <label>Base</label>
                        <select id="base_short" class="select2 base" style="width: 150px;">
                            @foreach($both as $item)
                                <option value="{{ $item->base }}">{{ $item->base }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Accounts</label>
                        <select id="account_short" class="select2 account" multiple="multiple" style="width: 300px;"></select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Type</label>
                        <select id="type_short" class="select2 type" style="width: 300px;">
                            <option value="All">All</option>
                            <option value="ACTIVE">Exclude Delete</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <div class="input-group">
                            <button type="button" class="btn btn-default form-control pull-right daterange" id="daterange_short">
                        <span>
                          <i class="fa fa-calendar"></i> Please select date range
                        </span>
                                <i class="fa fa-caret-down"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="chart_short" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
            <div style="margin-top: 50px;">
                <table id ="tbl_short_unique" class="table table-bordered table-striped table-hover-blue"></table>
            </div>
            <div>
                <table id ="tbl_short" class="table table-bordered table-striped table-hover-blue"></table>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('js/profit.js') }}"></script>

    <script>
        var columns = {
            "columns": [
                { "title": "#", "data": "number" },
                { "title": "Bot Name", "data": "name" },
                { "title": "Total Deals", "data": "total_deals" },
                { "title": "Total Profit", "data": "total_profit" },
                { "title": "Strategy", "data": "strategy" }
            ],
            "columnDefs": [ {
                "targets": 0,
                "data": "number",
                "render": rowNum
            } ]
        };

        var rangePickerOptions = {
            opens: "right",
            ranges   : {
                'Today'       : [moment(), moment()],
                'Yesterday'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days' : [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month'  : [moment().startOf('month'), moment().endOf('month')],
                'Last Month'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            startDate: moment().subtract(29, 'days'),
            endDate  : moment()
        };

        var rangeStartBoth, rangeEndBoth, rangeStartLong, rangeEndLong, rangeStartShort, rangeEndShort;
        var accountsBoth = accountsLong = accountsShort = [];
        var typeBoth = typeLong = typeShort = "ALL";
        var baseBoth = baseLong = baseShort = "";
        var strategy = "%";


        function makeReport(dealType) {
            $('.overlay').show();

            if (dealType == "%") {
                rangeStart = rangeStartBoth;
                rangeEnd = rangeEndBoth;
                base = baseBoth;
                accounts = accountsBoth;
                type = typeBoth;
            } else if (dealType == "Deal") {
                rangeStart = rangeStartLong;
                rangeEnd = rangeEndLong;
                base = baseLong;
                type = typeLong;
                accounts = accountsLong;
            } else if (dealType == "Deal::ShortDeal") {
                rangeStart = rangeStartShort;
                rangeEnd = rangeEndShort;
                base = baseShort;
                type = typeShort;
                accounts = accountsShort;
            }

            $.post("{{ route('profit/getBotByBase') }}", {
                "_token" : "{{ csrf_token() }}",
                "base" : base,
                "type" : type,
                "account": accounts,
                "strategy" : dealType,
                "start" : rangeStart != null ? rangeStart.format('YYYY-MM-DD 00:00:00') : "",
                "end" : rangeEnd != null ?  rangeEnd.format('YYYY-MM-DD 23:59:59') : "",
                "api_key" : "{{ $api_key }}",
            }, function (response) {
                    var series = [];
                    response.forEach(function (row, index) {
                        if (index < 20)
                            series.push({name:row.name, data:[row.total_profit]});
                    });

                    if (dealType == "%") {
                        $tableBoth.clear();
                        $tableBoth.rows.add(response);
                        $tableBoth.draw();

                        // $tableBothUnique.clear();
                        // $tableBothUnique.rows.add(response.filter(item => {
                        //     return item.name.indexOf("Deleted Bot ID") === -1;
                        // }));
                        // $tableBothUnique.draw();

                        Highcharts.chart('chart_both', {
                            chart: {
                                type: 'column',
                                padding: [0,0,0,0]
                            },
                            plotOptions: {
                                column: {
                                    groupPadding: 0
                                }
                            },
                            title: {
                                text: 'Bot Profit By Base Pair, Grouped By Quote'
                            },
                            yAxis: {
                                title: {
                                    text: 'Total profit'
                                },
                                tickInterval: 0.0001
                            },
                            xAxis: {
                                categories: ['Bot']
                            },
                            credits: {
                                enabled: false
                            },
                            series: series
                        });
                    } else if (dealType == "Deal") {
                        $tableLong.clear();
                        $tableLong.rows.add(response);
                        $tableLong.draw();

                        // $tableLongUnique.clear();
                        // $tableLongUnique.rows.add(response.filter(item => {
                        //     return item.name.indexOf("Deleted Bot ID") === -1;
                        // }));
                        // $tableLongUnique.draw();

                        Highcharts.chart('chart_long', {
                            chart: {
                                type: 'column',
                                padding: [0,0,0,0]
                            },
                            plotOptions: {
                                column: {
                                    groupPadding: 0
                                }
                            },
                            title: {
                                text: 'Long Profit By Base Pair, Grouped By Quote'
                            },
                            yAxis: {
                                title: {
                                    text: 'Total profit'
                                },
                                tickInterval: 0.0001
                            },
                            xAxis: {
                                categories: ['Long Bots']
                            },
                            credits: {
                                enabled: false
                            },
                            series: series
                        });
                    } else if (dealType == "Deal::ShortDeal") {
                        $tableShort.clear();
                        $tableShort.rows.add(response);
                        $tableShort.draw();

                        // $tableShortUnique.clear();
                        // $tableShortUnique.rows.add(response.filter(item => {
                        //     return item.name.indexOf("Deleted Bot ID") === -1;
                        // }));
                        // $tableShortUnique.draw();

                        Highcharts.chart('chart_short', {
                            chart: {
                                type: 'column',
                                padding: [0,0,0,0]
                            },
                            plotOptions: {
                                column: {
                                    groupPadding: 0
                                }
                            },
                            title: {
                                text: 'Short Profit By Base Pair, Grouped By Quote'
                            },
                            yAxis: {
                                title: {
                                    text: 'Total profit'
                                },
                                tickInterval: 0.0001
                            },
                            xAxis: {
                                categories: ['Short Bots']
                            },
                            credits: {
                                enabled: false
                            },
                            series: series
                        });
                    }

                    $('.overlay').hide();
            });

        }


        function rowNum(data, type, row, meta) {
            return meta.row + 1;
        }

        function updateAccounts(strategy, base) {
            $.post("{{ route('profit/getAccounts') }}", {
                "_token" : "{{ csrf_token() }}",
                "base" : base,
                "strategy" : strategy,
                "api_key" : "{{ $api_key }}",
            }, function (response) {
                var pairId = "account_both";

                if (strategy == "%") {
                    pairId = "account_both";
                    accountsBoth = [];
                } else if (strategy == "Deal") {
                    pairId = "account_long";
                    accountsLong = [];
                } else if (strategy == "Deal::ShortDeal") {
                    pairId = "account_short";
                    accountsShort = [];
                }
                $('#' + pairId).empty();
                response.forEach(function (row, index) {
                    $('#' + pairId).append('<option value="' + row.id + '">' + row.name + '</option>');
                });
                makeReport(strategy);
            });
        }


        $(function () {
            // $tableBothUnique = $('#tbl_both_unique').DataTable(columns);
            $tableBoth = $('#tbl_both').DataTable(columns);

            $tableLong = $('#tbl_long').DataTable(columns);
            // $tableLongUnique = $('#tbl_long_unique').DataTable(columns);

            $tableShort = $('#tbl_short').DataTable(columns);
            // $tableShortUnique = $('#tbl_short_unique').DataTable(columns);

            $('#daterange_both').daterangepicker(rangePickerOptions, function (start, end) {
                $('#daterange_both span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

                rangeStartBoth = start; rangeEndBoth = end;
                makeReport("%");
            });

            $('#daterange_long').daterangepicker(rangePickerOptions, function (start, end) {
                $('#daterange_long span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

                rangeStartLong = start; rangeEndLong = end;
                makeReport("Deal");
            });

            $('#daterange_short').daterangepicker(rangePickerOptions, function (start, end) {
                $('#daterange_short span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

                rangeStartShort = start; rangeEndShort = end;
                makeReport("Deal::ShortDeal");
            });

            $('.account').select2().on('change', function () {
                strategy = $(this).attr('id').split("_")[1];
                if (strategy == "long") {
                    console.log(strategy, "o");
                    accountsLong = $(this).val();
                    makeReport("Deal");
                } else if (strategy == "short") {
                    accountsShort = $(this).val();
                    makeReport("Deal::ShortDeal");
                } else {
                    accountsBoth = $(this).val();
                    makeReport("%");
                }
            });

            $('.type').select2().on('change', function () {
                strategy = $(this).attr('id').split("_")[1];
                if (strategy == "long") {
                    typeLong = $(this).val();
                    makeReport("Deal");
                } else if (strategy == "short") {
                    typeShort = $(this).val();
                    makeReport("Deal::ShortDeal");
                } else {
                    typeBoth = $(this).val();
                    makeReport("%");
                }
            });

            $('.base').select2().on('change', function () {
                var base = $(this).val();
                strategy = $(this).attr('id').split("_")[1];
                if (strategy == "long") {
                    var dealType = "Deal";
                    makeReport("Deal");
                    updateAccounts("Deal", baseLong);
                } else if (strategy == "short") {
                    var dealType = "Deal::ShortDeal";
                    updateAccounts("Deal::ShortDeal", baseShort);
                    makeReport("Deal::ShortDeal");
                } else {
                    var dealType = "%";
                    updateAccounts("%", baseBoth);
                    makeReport("%");
                }
            });


            @if (sizeof($both) > 0)
                $('#base_both').val('{{ $both[0]->base }}').trigger('change');
            @endif
            @if (sizeof($long) > 0)
                $('#base_long').val('{{ $long[0]->base }}').trigger('change');
            @endif
            @if (sizeof($short) > 0)
                $('#base_short').val('{{ $short[0]->base }}').trigger('change');
            @endif
        });
    </script>
@endsection