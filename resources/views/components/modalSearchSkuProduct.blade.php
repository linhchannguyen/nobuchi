<!-- load css and js order search -->
<link href="{{ asset('css/components/modalsearchskuproduct.css') }}" rel="stylesheet">
<script src="{{ asset('js/components/modalSearchSkuProduct.js') }}" ></script>
<!-- end  -->
<div class="modal fade" id="modal_product" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-custom" role="document">
        <div class="modal-content">
            <div class="modal-header header-modal-custom">
                <div class="name-company">
                    <h3 class="modal-title" id="exampleModalLabel">RimacECサイト管理システム</h3>
                    <h5 style="margin-left: 20px;">商品検索</h5>
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
                            <!-- delivery method -->
                            <div class="col-md-12 col-lg-12 col-sm-12">
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 col-sm-12">
                                        <div class="row">
                                            <div class="col-lg-1 col-md-1 col-sm-3">
                                                <label for="label-title">配送方法 </label>
                                            </div>
                                            <div class="col-lg-10 col-md-10 col-sm-10">
                                                <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="delivery_method_modal" value="1"> &nbsp; 佐川急便 &nbsp;&nbsp;</span>
                                                <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="delivery_method_modal" value="9"> &nbsp; 佐川急便(秘伝II) &nbsp;&nbsp;</span>
                                                <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="delivery_method_modal" value="2"> &nbsp; ヤマト宅急便 &nbsp;&nbsp;</span>
                                                <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="delivery_method_modal" value="3"> &nbsp; ネコポス &nbsp;&nbsp;</span>
                                                <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="delivery_method_modal" value="4"> &nbsp; コンパクト便 &nbsp;&nbsp;</span>
                                                <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="delivery_method_modal" value="5"> &nbsp; ゆうパック &nbsp;&nbsp;</span>
                                                <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="delivery_method_modal" value="6"> &nbsp; ゆうパケット &nbsp;&nbsp;</span>
                                                <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="delivery_method_modal" value="7"> &nbsp; 代引き &nbsp;&nbsp;</span>
                                                <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="delivery_method_modal" value="8"> &nbsp; その他 &nbsp;&nbsp;</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- status method -->
                            <div class="col-md-12 col-lg-12 col-sm-12">
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 col-sm-12">
                                        <div class="row">
                                            <div class="col-lg-1 col-md-1 col-sm-3">
                                                <label for="label-title">公開状況 </label>
                                            </div>
						                    <div class="col-lg-10 col-md-10 col-sm-10">
						                        <span class="check_flag_search"><input class="flag_search" type="radio" name="status_flg" value="1"  checked="checked"> &nbsp; 公開 &nbsp; &nbsp; &nbsp;</span>
						                        <span class="check_flag_search"><input class="flag_search" type="radio" name="status_flg" value="2"> &nbsp; 非公開 &nbsp; &nbsp; &nbsp; </span>
						                    </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- handling_flg method -->
                            <div class="col-md-12 col-lg-12 col-sm-12">
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 col-sm-12">
                                        <div class="row">
                                            <div class="col-lg-1 col-md-1 col-sm-3">
                                                <label for="label-title">取扱状況 </label>
                                            </div>
						                    <div class="col-lg-10 col-md-10 col-sm-10">
						                        <span class="check_flag_search"><input class="flag_search" type="radio" name="handling_flg" value="0"  checked="checked"> &nbsp; 取扱 &nbsp; &nbsp; &nbsp;</span>
						                        <span class="check_flag_search"><input class="flag_search" type="radio" name="handling_flg" value="1"> &nbsp; 終売 &nbsp; &nbsp; &nbsp; </span>
						                    </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- other -->
                            <div class="col-md-12 col-lg-12 col-sm-12">
                                <div class="row">
                                    <div class="col-lg-1 col-md-1 col-sm-3">
                                        <label for="label-title">その他  </label>
                                    </div>
                                    <div class="col-lg-10 col-md-10 col-sm-10">
                                        <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="orther_modal" value="6"> &nbsp; 冷蔵 &nbsp; &nbsp; &nbsp; </span>
                                        <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="orther_modal" value="7"> &nbsp; 冷凍 &nbsp; &nbsp; &nbsp; </span>
                                        <!-- <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="orther_modal" value="8"> &nbsp; 送料無料 &nbsp; &nbsp; &nbsp; </span>
                                        <span class="check_flag_stage"><input type="checkbox" class="flag_stage" name="orther_modal" value="9"> &nbsp; 自社在庫 &nbsp; &nbsp; &nbsp; </span> -->
                                    </div>
                                </div>
                            </div>
                            <!-- sku and  仕入先 -->
                            <div class="col-md-12 col-lg-12 col-sm-12">
                                <div class="row row-input">
                                    <div class="col-lg-1 col-md-1 col-sm-3">
                                        <label for="label-title">SKU</label>
                                    </div>
                                    <div class="col-lg-3 col-md-3 col-sm-10">
                                        <input type="text" id="sku_modal">
                                    </div>
                                    <div class="col-lg-1 col-md-1 col-sm-3">
                                        <label for="label-title">仕入先名 </label>
                                    </div>
                                    <div class="col-lg-4 col-md-4 col-sm-10">
                                        <input type="text" id="supplied_modal">
                                    </div>
                                </div>
                            </div>
                            <!-- chỉ định phân loại -->
                            <div class="col-md-12 col-lg-12 col-sm-12">
                                <div class="row  control-select">
                                    <div class="col-lg-1 col-md-1 col-sm-3">
                                        <label for="label-title">分類指定 </label>
                                    </div>
                                    <div class="col-lg-4 col-md-4 col-sm-10">
                                        <select class="form-control form-control-sm category-select-modal" id="category_modal">
                                            <option value="">選択してください</option>
                                            <option value="1">大分類</option>
                                            <option value="2">中分類</option>
                                            <option value="3">小分類</option>
                                            <option value="4">その他1</option>
                                            <option value="5">その他2</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-4 col-md-4 col-sm-10">
                                        <select class="form-control form-control-sm product-select-modal" id="product_modal">
                                            <option value=""></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- ten san pham -->
                            <div class="col-md-12 col-lg-12 col-sm-12">
                                <div class="row row-input" style="padding-top: 10px;">
                                    <div class="col-lg-1 col-md-1 col-sm-3">
                                        <label for="label-title">品名 </label>
                                    </div>
                                    <div class="col-lg-8 col-md-8 col-sm-8">
                                        <input type="text" id="name_product_modal">
                                    </div>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-search-order" id="search_product_modal">検索</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- table results -->
                <div class="card-table-results">
                    <!-- table -->
                    <table class="table" id="table_products">
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var url_search_sku = "{{url('/product/search-sku')}}";
    var urlGetCategoryModal = "{{url('/product/categories')}}";
</script>