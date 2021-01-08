<!-- Left side column. contains the sidebar -->
<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar Menu -->
        <ul class="sidebar-menu" data-widget="tree">
            <!--li class="header">HEADER</li-->
            <!-- Optionally, you can add icons to the links -->
            <li class="{{ request()->is('dashboard*') ? 'active' : '' }}">
                <a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a>
            </li>
            <li class="{{ request()->is('profit*') ? 'active menu-open' : '' }} treeview">
                <a href="#">
                    <i class="fa fa-bar-chart"></i>
                    <span>Profit</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->is('profit/date*') ? 'active' : '' }}"><a href="{{ url('profit/date') }}"><i class="fa fa-calendar"></i> Profit by Date</a></li>
                    <li class="{{ request()->is('profit/pair*') ? 'active' : '' }}"><a href="{{ url('profit/pair') }}"><i class="fa fa-clone"></i> Profit by Pair</a></li>
                    <li class="{{ request()->is('profit/bot*') ? 'active' : '' }}"><a href="{{ url('profit/bot') }}"><i class="fa fa-server"></i> Profit by Bot</a></li>
                </ul>
            </li>
            <li class="{{ request()->is('calculator*') ? 'active menu-open' : '' }} treeview">
                <a href="#">
                    <i class="fa fa-calculator"></i>
                    <span>Bot Calculator</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->is('calculator/longBot*') ? 'active' : '' }}"><a href="{{ url('calculator/longBot') }}"><i class="fa fa-arrow-circle-up"></i> Long Bot</a></li>
                    <li class="{{ request()->is('calculator/shortBot*') ? 'active' : '' }}"><a href="{{ url('calculator/shortBot') }}"><i class="fa fa-arrow-circle-down"></i> Short Bot</a></li>
                </ul>
            </li>
            <li class="{{ request()->is('basic*') ? 'active menu-open' : '' }} treeview">
                <a href="#">
                    <i class="fa fa-pied-piper-alt"></i>
                    <span>Basic Info</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->is('basic/bot*') ? 'active' : '' }}"><a href="{{ url('basic/bot') }}"><i class="fa fa-rocket"></i> Bots</a></li>
                    <li class="{{ request()->is('basic/deal*') ? 'active' : '' }}"><a href="{{ url('basic/deal') }}"><i class="fa fa-truck"></i> Deals</a></li>
                </ul>
            </li>
            <li class="{{ request()->is('smartswitch*') ? 'active menu-open' : '' }} treeview">
                <a href="#">
                    <i class="fa fa-refresh"></i>
                    <span>SmartSwitch</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->is('smartswitch/dual/*') ? 'active' : '' }}"><a href="{{ url('smartswitch/dual/') }}"><i class="fa fa-hand-peace-o"></i> Dual Bot<small class="label pull-right bg-primary">New!</small></span></a></li>
                </ul>
            </li>

            <li class="{{ request()->is('3commas*') ? 'active menu-open' : '' }} treeview">
                <a href="#">
                    <i class="fa fa-refresh"></i>
                    <span>Accounts/Pairs</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->is('user/accounts/index') ? 'active' : '' }}"><a href="{{ route('user.accounts.index') }}"><i class="fa fa-hand-peace-o"></i> Assign User Accounts<small class="label pull-right bg-primary">New!</small></span></a></li>
                </ul>
            </li>

            <li class="{{ request()->is('3commas*') ? 'active menu-open' : '' }} treeview">
                <a href="#">
                    <i class="fa fa-refresh"></i>
                    <span>API Actions</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ request()->is('scheduler') ? 'active' : '' }}"><a href="{{ route('scheduler.index') }}"><i class="fa fa-hand-peace-o"></i> Crons<small class="label pull-right bg-primary">New!</small></span></a></li>
                    <li class="{{ request()->is('3commas/loadDeal') ? 'active' : '' }}"><a href="{{ url('3commas/loadDeal') }}"><i class="fa fa-hand-peace-o"></i> Load Deals<small class="label pull-right bg-primary">New!</small></span></a></li>
                    <li class="{{ request()->is('3commas/loadBots') ? 'active' : '' }}"><a href="{{ url('3commas/loadBots') }}"><i class="fa fa-hand-peace-o"></i> Load Bots<small class="label pull-right bg-primary">New!</small></span></a></li>
                    <li class="{{ request()->is('3commas/loadAccounts') ? 'active' : '' }}"><a href="{{ url('3commas/loadAccounts') }}"><i class="fa fa-hand-peace-o"></i> Load Accounts<small class="label pull-right bg-primary">New!</small></span></a></li>
                    <li class="{{ request()->is('run/monitor') ? 'active' : '' }}"><a href="{{ url('run/monitor') }}"><i class="fa fa-hand-peace-o"></i> Run Monitor<small class="label pull-right bg-primary">New!</small></span></a></li>
                    <li class="{{ request()->is('stop/monitor') ? 'active' : '' }}"><a href="{{ url('stop/monitor') }}"><i class="fa fa-hand-peace-o"></i> Stop Monitor<small class="label pull-right bg-primary">New!</small></span></a></li>
                </ul>
            </li>
        </ul><!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>