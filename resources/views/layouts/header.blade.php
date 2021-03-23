<div class="header">
	<div class="header-border">
		<h3><a href="{{ url('/') }}" class="logo-text">RimacECサイト管理システム</a></h3>
		<div class="container-fluid">
			<div class="header-title" style="{{ 
				(Session::get('login', true) && (Session::get('type') == 1|| Session::get('type') == 0)) ?
					'border-bottom: 1px solid #000; padding-left: 30px;' : 'padding-bottom: 10px;'
				}}">
				<h5><b>{{ !empty($title) ? $title : '' }}</b></h5>
					<li class="header-right dropdown" style ="{{(Session::get('login', true) && (Session::get('type') == 1 || Session::get('type') == 0)) ?'top: 3.5%;' : 'top: 4%;'}}">
						<a class="dropdown-toggle dropbtn"><i class="fa fa-user"></i> <b class="caret">{{Session::get('name')}}</b></a>
						<ul id="myDropdown" class="dropdown-content" style="top: 30px; right: 12px; border-radius: 5px;">
							<li>
								<div>
								<a href="{{url('logout')}}">
									ログアウト 
								</a>
								<div class="arrow-up"></div>
								</div>
							</li>
						</ul>
					</li>
				<!-- </span> -->
			</div>
			@if(Session::get('login', true) && (Session::get('type') == 1 || Session::get('type') == 0))
				@include('layouts.nav')
			@endif
		</div>
	</div>
</div>