<script type="text/javascript">

var ajaxGetCartCount='{{route('architect.gift.products.cart.count')}}';
var ajaxGetSetCart='{{route('architect.gift.products.cart.set')}}';
var ajaxRemoveFromCart='{{route('architect.gift.products.cart.remove')}}';
var ajaxCartDetail='{{route('architect.gift.products.cart.detail')}}';




	function getCartItemCount(){


		$.ajax({
        type: 'GET',
        url: ajaxGetCartCount ,
        success: function(resultData) {
            if (resultData['status'] == 1) {

                $("#cartItemCount").html(resultData['data']);



            } else {

                toastr["error"](resultData['msg']);

            }

        }
    });

	}


    function setCartItem(id){


        $.ajax({
        type: 'GET',
        url: ajaxGetSetCart+"?id="+id ,
        success: function(resultData) {
            if (resultData['status'] == 1) {

                getCartItemCount();
                $("#CartBtnLable").addClass('highlight-cart-btn');
                setTimeout(function(){
                    $("#CartBtnLable").removeClass('highlight-cart-btn');
                }, 1000);

                toastr["success"](resultData['msg']);


            } else {

                toastr["error"](resultData['msg']);

            }

        }
    });

    }

      function removeFromCart(id){


        $.ajax({
        type: 'GET',
        url: ajaxRemoveFromCart+"?id="+id ,
        success: function(resultData) {
            if (resultData['status'] == 1) {

                getCartItemCount();
                getCartDetail();
                $("#CartBtnLable").addClass('highlight-cart-btn');
                setTimeout(function(){
                    $("#CartBtnLable").removeClass('highlight-cart-btn');
                }, 1000);


                toastr["success"](resultData['msg']);
            } else {

                toastr["error"](resultData['msg']);

            }

        }
    });

    }

       function getCartDetail(){

         $("#cartDetailView").html('<div class="text-center">Loading...</div>');


        $.ajax({
        type: 'GET',
        url:ajaxCartDetail ,
        success: function(resultData) {
            if (resultData['status'] == 1) {

                $("#cartDetailView").html(resultData['cart_html']);

            } else {

                toastr["error"](resultData['msg']);

            }

        }
    });

    }
getCartItemCount();

</script>