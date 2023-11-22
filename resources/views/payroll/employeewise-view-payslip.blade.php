@extends('payroll.include.app')
@section('content')
<div class="main-panel">
   <div class="page-header">
      <!-- <h4 class="page-title">Attendance Management</h4> -->
      <ul class="breadcrumbs">
         <li class="nav-home"><a href="{{url('payroll-home-dashboard')}}"> Home</a></li>
         <li class="separator"> / </li>
         <li class="nav-item"><a href="{{url('payroll/dashboard')}}">Payroll</a></li>
         <li class="separator"> / </li>
         <li class="nav-item active">Employee Wise Payslip</li>
      </ul>
   </div>
   <div class="content">
      <div class="page-inner">
         <div class="row">
            <div class="col-md-12">
               <div class="card custom-card">
                @include('layout.message')
                  <div class="card-body">

                        <!--Search Payslip-->
                        <form style="padding: 5px 10px 15px 20px !important;" action="{{ url('payroll/vw-employeewise-view-payslip') }}" method="post">
                            <h5 class="card-title">Employee Wise Payslip</h5>
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="row form-group">
                                <div class="col-md-4">
                                    <label for="text-input" class=" form-control-label">Select Month
                                        <span>(*)</span></label>
                                    <select data-placeholder="Choose an Month..." class="form-control" name="month_yr"
                                        id="month_yr" required>
                                        <option value="" selected disabled> Select </option>
                                        <?php foreach ($monthlist as $month) {?>
                                        <option value="<?php echo $month->month_yr; ?>"><?php echo $month->month_yr; ?></option>
                                        <?php }?>
                                    </select>
                                    @if ($errors->has('month_yr'))
                                        <div class="error" style="color:red;">{{ $errors->first('month_yr') }}</div>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <label class=" form-control-label">Enter Employee Id <span>(*)</span></label>
                                    <select data-placeholder="Choose Employee..." name="emp_code"
                                        class="form-control select2_el" required>
                                        <option value="" selected disabled> Select </option>
                                        <?php foreach ($employeeslist as $employee) {?>
                                        <option value="<?php echo $employee->emp_code; ?>"
                                            @if (isset($emp_id_new) && $emp_id_new == $employee->emp_code) selected @endif><?php echo $employee->emp_fname . ' ' . $employee->emp_mname . ' ' . $employee->emp_lname . ' (' . $employee->old_emp_code . ') '; ?>
                                        </option>
                                        <?php }?>
                                    </select>


                                    @if ($errors->has('emp_code'))
                                        <div class="error" style="color:red;">{{ $errors->first('emp_code') }}</div>
                                    @endif
                                </div>

                                <div class="col-md-4 btn-up modifi_css">
                                    <button type="submit" class="btn btn-info btn-sm ps-3 pe-3">View </button>
                                </div>
                            </div>
                        </form>
                        <!--End-->

                    <!--Send Payslip Mail-->
                    <form style="padding: 5px 10px 15px 20px !important;" action="{{ url('payroll/payslip/mail-to-employee') }}" method="post">
                        <h5 class="card-title">Send Payslip To Employees</h5>
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="row form-group">
                            <div class="col-md-5">
                                <label for="text-input" class=" form-control-label">Select Month
                                    <span>(*)</span></label>
                                <select data-placeholder="Choose an Month..." class="form-control" name="month_yr"
                                    id="month_yr" required>
                                    <option value="" selected disabled> Select </option>
                                    <?php foreach ($monthlist as $month) {?>
                                    <option value="<?php echo $month->month_yr; ?>"><?php echo $month->month_yr; ?></option>
                                    <?php }?>
                                </select>
                                @if ($errors->has('month_yr'))
                                    <div class="error" style="color:red;">{{ $errors->first('month_yr') }}</div>
                                @endif
                            </div>
                            <div class="col-md-4 btn-up modifi_css">
                                <button type="submit" class="btn btn-info btn-sm ps-3">
                                Send
                                </button>
                            </div>
                        </div>
                    </form>
                    <!--End-->
                  </div>
               </div>
            </div>
         </div>
         @if($result !='')
            <div class="row">
                <div class="col-md-12">
                <div class="card">
                    {{-- <div class="card-header">
                        <h4 class="card-title"><i class="fa fa-cog" aria-hidden="true" style="color:#10277f;"></i>&nbsp;Process Attendance</h4>
                    </div> --}}
                    <div class="card-body">
                        <div class="table-responsive">
                                <table id="bootstrap-data-table" class="table table-striped table-bordered">
                                    <thead style="text-align:center;vertical-align: middle;">
                                        <tr style="font-size:11px;text-align:center">
                                            <th>Employee Code</th>
                                            <th>Employee Name</th>
                                            <th>Designation</th>
                                            <th>Month</th>
                                            <th>Gross Salary</th>
                                            <th>Total Deductions</th>
                                            <th>Net Salary</th>
                                            <th>View</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php print_r($result); ?>
                                    </tbody>
                                </table>
                        </div>
                    </div>
                </div>
                </div>
            </div>
         @endif
      </div>
   </div>

   @endsection
    @section('js')

        <script src="{{ asset('js/select2.min.js') }}"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                initailizeSelect2();
            });
            // Initialize select2
            function initailizeSelect2() {
    
                $(".select2_el").select2();
            }
        </script>

    @endsection
