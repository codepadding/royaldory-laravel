@extends('layouts.admin')

@section('content')

    <div class="content-area">
        <div class="mr-breadcrumb">
            <div class="row">
                <div class="col-lg-12">
                    <h4 class="heading">{{ __('Referal Informations') }}</h4>
                    <ul class="links">
                        <li>
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }} </a>
                        </li>
                        <li>
                            <a href="javascript:;">{{ __('General Settings') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('admin-gs-affilate') }}">{{ __('Refer & Earn') }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="add-product-content1">
            <div class="row">
                <div class="col-lg-12">
                    <div class="product-description">
                        <div class="body-area">
                            <div class="gocover"
                                 style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
                            <form action="{{ route('admin-gs-update') }}" id="geniusform" method="POST"
                                  enctype="multipart/form-data">
                                {{ csrf_field() }}

                                @include('includes.admin.form-both')

                                <div class="row justify-content-center">
                                    <div class="col-lg-3">
                                        <div class="left-area">
                                            <h4 class="heading">
                                                {{ __('Refer & Earn') }}
                                            </h4>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="action-list">
                                            <select class="process select droplinks {{ $gs->is_refferal == 1 ? 'drop-success' : 'drop-danger' }}">
                                                <option data-val="1" value="{{route('admin-gs-isrefferal',1)}}" {{ $gs->is_refferal == 1 ? 'selected' : '' }}>{{ __('Activated') }}</option>
                                                <option data-val="0" value="{{route('admin-gs-isrefferal',0)}}" {{ $gs->is_refferal == 0 ? 'selected' : '' }}>{{ __('Deactivated') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row justify-content-center">
                                    <div class="col-lg-3">
                                        <div class="left-area">
                                            <h4 class="heading">{{ __('Points per refferal') }}</h4>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="number" class="input-field"
                                               placeholder="{{ __('10') }}" name="refferal_point"
                                               value="{{ $gs->refferal_point }}" required="">
                                    </div>
                                </div>


                                <div class="row justify-content-center">
                                    <div class="col-lg-3">
                                        <div class="left-area">

                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <button class="addProductSubmit-btn" type="submit">{{ __('Save') }}</button>
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