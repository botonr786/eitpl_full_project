<div class="sidebar sidebar-style-2">			
			<div class="sidebar-wrapper scrollbar scrollbar-inner">
				<div class="sidebar-content">
					<div class="user">
						<div class="avatar-sm float-left mr-2">
							<img src="{{ asset('assets/img/profile.png')}}" alt="..." class="avatar-img rounded-circle">
						</div>
						<div class="info">
							<a data-toggle="collapse" href="#collapseExample" aria-expanded="true">
								<span>
								Internal User Rota
								
									<span class="caret"></span>
								</span>
							</a>
							<div class="clearfix"></div>

							<div class="collapse in" id="collapseExample">
								<ul class="nav">
									
									<li>
									 @if(Session::get('admin_userp_user_type')=='user')
										<a href="{{url('mainuesrLogout')}}">
										@else
										<a href="{{url('mainLogout')}}">
										    	@endif
											<span class="link-collapse">Logout</span>
										</a>
									</li>
								</ul>
							</div>
						</div>
					</div>
					<ul class="nav nav-primary">
						<li class="nav-item active">
							<a href="{{url('interroatadashboard')}}">
								<i class="fas fa-home"></i>
								<p>Dashboard</p>
								<span class="caret"></span>
							</a>
							
						</li>
						<li class="nav-section">
							<span class="sidebar-mini-icon">
								<i class="fa fa-ellipsis-h"></i>
							</span>
							
						</li>
						<li class="nav-item">
							<a data-toggle="collapse" href="#sidebarLayoutjstime">
								<i class="fas fa-layer-group"></i>
								<p>Time Shift Management
</p>
								<span class="caret"></span>
							</a>
							<div class="collapse" id="sidebarLayoutjstime">
								<ul class="nav nav-collapse">
									<li>
										<a href="{{url('interroata/view-time-schedule')}}">
											<span class="sub-item">Time Schedule</span>
										</a>
									</li>
						
				
<li>
										<a href="{{url('interroata/offday')}}">
											<span class="sub-item">Day off </span>
										</a>
									</li>
										
<li>
										<a href="{{url('interroata/duty-roster')}}">
											<span class="sub-item">Duty Roster </span>
										</a>
									</li>			
													
							
														
<li>
										<a href="{{url('interroata/work-update')}}">
											<span class="sub-item">Daily Work Update </span>
										</a>
									</li>			
															
								</ul>
							</div>
						</li>
						<!--<li class="nav-item">
							<a href="company.php">
								<i class="fas fa-layer-group"></i>
								<p>Company</p>
								
							</a>
							
						</li>-->
						
					
					
					
						
					</ul>
				</div>
			</div>
		</div>