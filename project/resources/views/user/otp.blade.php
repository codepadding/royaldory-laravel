@extends('layouts.front')
@section('styles')
  <style>
    #wrapper {
      font-family: Lato;
      font-size: 1.5rem;
      text-align: center;
      box-sizing: border-box;
      color: #333;
    }
    #dialog {
      border: solid 1px #ccc;
      margin: 10px auto;
      padding: 20px 30px;
      display: inline-block;
      box-shadow: 0 0 4px #ccc;
      background-color: #FAF8F8;
      overflow: hidden;
      position: relative;
      max-width: 450px;
    }
    h3 {
      margin: 0 0 10px;
      padding: 0;
      line-height: 1.25;
    }

    span {
      font-size: 90%;
    }

    #form {
      max-width: 300px;
      margin: 25px auto 0;
    }
    input.otpdigit {
      margin: 0 5px;
      text-align: center;
      line-height: 80px;
      font-size: 50px;
      border: solid 1px #ccc;
      box-shadow: 0 0 5px #ccc inset;
      outline: none;
      width: 20%;
      transition: all .2s ease-in-out;
      border-radius: 3px;
    }
    a{
      color: #007bff;
    }
    button.submit-btn:disabled{
      background: gray !important;
    }

  </style>
@endsection
@section('content')

<section class="login-signup">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-6">

        <div class="tab-content" id="nav-tabContent">
          <div class="tab-pane fade show active" id="nav-log" role="tabpanel" aria-labelledby="nav-log-tab">
            <div class="login-area">
              <div class="header-area">
                <h4 class="title">Verify Your Number</h4>
              </div>
              <div class="login-form signin-form">
                  <div id="wrapper">
                    <div id="dialog">
                      @if(session()->has('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert" style="font-size: 20px">
                          <strong class="text-success">Info! </strong> {{session()->get('success')}}
                          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                          </button>
                        </div>
                      @endif
                      @if(session()->has('alert'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert" style="font-size: 20px">
                          <strong class="text-danger">Warning! </strong> {{session()->get('alert')}}
                          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                          </button>
                        </div>
                      @endif


                    <form class="otpform" action="{{ route('user.checkotp') }}" method="POST">

                      @include('includes.admin.form-login')
                      {{ csrf_field() }}
                      <h5>Please enter the 4-digit verification code we sent via SMS:</h5>
                      <input type="hidden" id="otp" name="otp">
                      <div id="form">
                        <input type="text" class="otpdigit" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" />
                        <input type="text" class="otpdigit" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" />
                        <input type="text" class="otpdigit" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" />
                        <input type="text" class="otpdigit" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" />
                      </div>
                        <input class="mprocessdata" type="hidden" value="{{ $langg->lang188 }}">
                        <button class="submit-btn" style="width: 60%; height: 40px" disabled>Verify</button>

                      <div class="mt-2 mb-2">
                        <h5>Didn't receive the code?</h5>
                        <a href="{{route('user.sendotp')}}" disabled="">Send code again</a> Or
                        <a href="#" class="change_phone">Change phone number</a>
                      </div>
                  </form>

                    <form class="form-inline d-none" id="change_form" action="{{route('user.sendotp')}}" method="GET">
                      <div class="input-group ml-auto mr-auto">
                        <input type="text" name="phone" class="form-control" placeholder="017XXXXXXXX" required>
                        <button class="btn btn-sm btn-primary" type="submit">Update</button>
                      </div>
                      @if($errors->has('phone'))
                        <small class="text-danger ml-auto mr-auto" style="font-size: 65%">{{ $errors->first('phone') }}</small>
                      @endif

                    </form>
                    </div>

                  </div>

              </div>
            </div>
          </div>

        </div>

      </div>

    </div>
  </div>
</section>

@endsection

@section('scripts')
  <script>
    $(function() {
      'use strict';

      var body = $('body .otpform');

      function goToNextInput(e) {
        var key = e.which,
                t = $(e.target),
                sib = t.next('input');

        if (key != 9 && (key < 48 || key > 57)) {
          e.preventDefault();
          return false;
        }

        if (key === 9) {
          return true;
        }

        if (!sib || !sib.length) {
          sib = body.find('input').eq(0);
        }
        sib.select().focus();
      }

      function onKeyDown(e) {
        var key = e.which;

        if (key === 9 || (key >= 48 && key <= 57)) {
          return true;
        }

        e.preventDefault();
        return false;
      }

      function onFocus(e) {
        $(e.target).select();
      }

      body.on('keyup', 'input', goToNextInput);
      body.on('keydown', 'input', onKeyDown);
      body.on('click', 'input', onFocus);

    })

    $(document).ready(function () {
      var Otpval;
      $('.otpdigit').on('keyup',function () {
        $('.otpdigit').each(function () {
          if($(this).val()==''){
            Otpval=false;
          }else{
            Otpval=true;
          }

        });

        if(Otpval){
          Otpval='';
          $('.otpdigit').each(function () {
              Otpval+=$(this).val();
          });
        }

        if(Otpval.length==4){
          $('#otp').val(Otpval);
          $('button.submit-btn').attr('disabled',false);
        }else{
          $('button.submit-btn').attr('disabled',true);

        }

      });

      $('a.change_phone').on('click',function (e) {
        e.preventDefault();
          $('form#change_form').removeClass('d-none');
      });
      setTimeout(function () {
        $('ul.errors, div.alert').fadeOut('slow');
      },5000);


    })
  </script>
@endsection

