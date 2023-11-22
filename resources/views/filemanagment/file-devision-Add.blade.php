@extends('filemanagment.include.include')
@section('content')
<div class="main-panel">
   <div class="page-header">
      <!-- <h4 class="page-title">Attendance Management</h4> -->
      <ul class="breadcrumbs">
         <li class="nav-home">
            <a href="#">
            Home
            </a>
         </li>
         <li class="separator">
            /
         </li>
         <li class="nav-item active">
            <a href="#">Add File Managment</a>
         </li>
      </ul>
   </div>
   <div class="content">
      <div class="page-inner">
         <div class="row">
            <div class="col-md-12">
               <div class="card custom-card">
                  @if(Session::has('message'))										
                  <div class="alert alert-success" style="text-align:center;"><span class="glyphicon glyphicon-ok" ></span><em > {{ Session::get('message') }}</em></div>
                  @endif
                  <div class="card-body">
                    <form  method="post" action="{{url('fileManagment/fileManagment-division-adds')}}" enctype="multipart/form-data" >
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                   <label>Name</label>
                                   <input type="text" class="form-control" name="name">
                                </div>
                                </div>
                            <div class="col-md-4">
                            <div class="form-group">
                               <label>File Name</label>
                               <select name="file_name" class="form-control" id="">
                                <option></option>
                                @foreach($file_details as $item)
                                <option value="<?php print_r($item->file_name) ?>">{{$item->file_name}}</option>
                                @endforeach
                               </select>
                              
                            </div>
                            </div>
                           <div class="col-md-4">
                              <div class="form-group">
                                <label>Employee Code</label>
                                <input type="text" class="form-control" name="emp_id" value="<?php print_r($emp_details->emid) ?>" readonly>
                              </div>
                           </div>
                           <div class="col-md-4">
                            <div class="form-group">
                              <label>Division</label>
                              <input type="text" class="form-control" name="division">
                            </div>
                         </div>
                         <div class="col-md-4">
                            <div class="form-group">
                              <label>Employee Name</label>
                              <input type="text" class="form-control" name="emp_name" value="<?php print_r($emp_details->emid) ?>" readonly>
                            </div>
                         </div>
                           <div class="col-md-4">
                            <div class="form-group">
                                <label>Organization</label>
                                <input type="text" class="form-control" name="organization_id" value="<?php print_r($emp_details->emp_code) ?>" readonly>
                            </div>
                         </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label>Status</label>
                                <select class="form-control" name="status">
                                <option>Status</option>
                                <option value="active">Active</option>
                                <option value="inActive">InActive</option>
                            </select>
                            </div>
                         </div>
                        </div>
                        <div class="row form-group">
                            
                           <div class="col-md-3">
                              <a href="#">	
                              <button class="btn btn-default" type="submit" style="background-color: #1572E8!important; color: #fff!important;">Go</button></a>
                           </div>
                        </div>
                     </form>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>

   @endsection
   @section('js')
{{-- @include('payroll.partials.scripts') --}}
<script>
	
</script>
@endsection
   