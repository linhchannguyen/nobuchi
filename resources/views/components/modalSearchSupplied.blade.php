<!-- load css and js order search -->
<link href="{{ asset('css/components/modalsearchsupplied.css') }}" rel="stylesheet">
<script src="{{ asset('js/components/modalSearchSupplied.js') }}" ></script>
<!-- end  -->
<div class="modal fade" id="modal_supplied" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-custom" role="document">
        <div class="modal-content">
            <div class="modal-header header-modal-custom">
                <div class="name-company">
                    <h3 class="modal-title" id="exampleModalLabel">RimacECサイト管理システム</h3>
                    <h5 style="margin-left: 20px;">仕入先検索</h5>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card-search-modal">
                    <div class="title-card-search">
                        <h5>絞り込み条件</h5>
                    </div>
                    <div class="body-card-search">
                        <div class="form-radio-custom">
                            <!-- supplied type -->
                            <div class="col-md-12 col-lg-12 col-sm-12">
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 col-sm-12">
                                        <div class="row">
                                            <div class="col-lg-1 col-md-1 col-sm-3">
                                                <label for="label-title">分類 </label>
                                            </div>
                                            <div class="col-lg-10 col-md-10 col-sm-10">
                                                <span class="check_flag_search"><input class="flag_search" type="radio" name="modal_sup_type" value="1"> &nbsp; 自社 &nbsp; &nbsp; &nbsp; </span>
                                                <span class="check_flag_search"><input class="flag_search" type="radio" name="modal_sup_type" value="2"> &nbsp; 仕入・発送 &nbsp; &nbsp; &nbsp; </span>
                                                <span class="check_flag_search"><input class="flag_search" type="radio" name="modal_sup_type" value="3"> &nbsp; 仕入のみ &nbsp; &nbsp; &nbsp; </span>
                                                <span class="check_flag_search"><input class="flag_search" type="radio" name="modal_sup_type" value="4"> &nbsp; 発送のみ &nbsp; &nbsp; &nbsp; </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- delivery method -->
                            <div class="col-md-12 col-lg-12 col-sm-12">
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 col-sm-12">
                                        <div class="row">
                                            <div class="col-lg-1 col-md-1 col-sm-3">
                                                <label for="label-title">配送方法 </label>
                                            </div>
                                            <div class="col-lg-10 col-md-10 col-sm-10">
                                                <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="modal_sup_delivery_method" value="1"> &nbsp; 佐川急便 &nbsp;&nbsp;</span>
                                                <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="modal_sup_delivery_method" value="9"> &nbsp; 佐川急便(秘伝II) &nbsp;&nbsp;</span>
                                                <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="modal_sup_delivery_method" value="2"> &nbsp; ヤマト宅急便 &nbsp;&nbsp;</span>
                                                <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="modal_sup_delivery_method" value="3"> &nbsp; ネコポス &nbsp;&nbsp;</span>
                                                <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="modal_sup_delivery_method" value="4"> &nbsp; コンパクト便 &nbsp;&nbsp;</span>
                                                <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="modal_sup_delivery_method" value="5"> &nbsp; ゆうパック &nbsp;&nbsp;</span>
                                                <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="modal_sup_delivery_method" value="6"> &nbsp; ゆうパケット &nbsp;&nbsp;</span>
                                                <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="modal_sup_delivery_method" value="7"> &nbsp; 代引き &nbsp;&nbsp;</span>
                                                <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="modal_sup_delivery_method" value="8"> &nbsp; その他 &nbsp;&nbsp;</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- other -->
                            <div class="col-md-12 col-lg-12 col-sm-12">
                                <div class="row">
                                    <div class="col-lg-1 col-md-1 col-sm-3">
                                        <label for="label-title">発注方法  </label>
                                    </div>
                                    <div class="col-lg-10 col-md-10 col-sm-10">
                                        <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="modal_sup_purchase_method" value="0"> &nbsp; FAX &nbsp; &nbsp; &nbsp; </span>
                                        <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="modal_sup_purchase_method" value="1"> &nbsp; EDI &nbsp; &nbsp; &nbsp; </span>
                                        <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="modal_sup_purchase_method" value="2"> &nbsp; メール &nbsp; &nbsp; &nbsp; </span>
                                        <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="modal_sup_purchase_method" value="3"> &nbsp; その他 &nbsp; &nbsp; &nbsp; </span>
                                    </div>
                                </div>
                            </div>
                            <!-- delivery method -->
                            <div class="col-md-12 col-lg-12 col-sm-12">
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 col-sm-12">
                                        <div class="row">
                                            <div class="col-lg-1 col-md-1 col-sm-3">
                                                <label for="label-title">休業日 </label>
                                            </div>
                                            <div class="col-lg-10 col-md-10 col-sm-10">
                                                <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="modal_sup_date_off" value="holiday_mon"> &nbsp; 月曜日 &nbsp; &nbsp; &nbsp; </span>
                                                <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="modal_sup_date_off" value="holiday_tue"> &nbsp; 火曜日 &nbsp; &nbsp; &nbsp; </span>
                                                <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="modal_sup_date_off" value="holiday_wed"> &nbsp; 水曜日 &nbsp; &nbsp; &nbsp; </span>
                                                <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="modal_sup_date_off" value="holiday_thu"> &nbsp; 木曜日 &nbsp; &nbsp; &nbsp; </span>
                                                <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="modal_sup_date_off" value="holiday_fri"> &nbsp; 金曜日 &nbsp; &nbsp; &nbsp; </span>
                                                <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="modal_sup_date_off" value="holiday_sat"> &nbsp; 土曜日 &nbsp; &nbsp; &nbsp; </span>
                                                <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="modal_sup_date_off" value="holiday_sun"> &nbsp; 日曜日 &nbsp; &nbsp; &nbsp; </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- supplied name -->
                            <div class="col-md-12 col-lg-12 col-sm-12">
                                <div class="row row-input" style="padding-top: 10px;">
                                    <div class="col-lg-1 col-md-1 col-sm-3">
                                        <label for="label-title">集荷先名 </label>
                                    </div>
                                    <div class="col-lg-8 col-md-8 col-sm-8">
                                        <input type="text" id="modal_sup_name">
                                    </div>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-search-order" id="search_supplier_modal">検索</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- table results -->
                <div class="card-table-results">
                    <!-- table -->
                    <table class="table" id="table_modal_sup_result">
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var url_search_supplier = "{{url('/supplier/search-modal-supplier')}}";
</script>