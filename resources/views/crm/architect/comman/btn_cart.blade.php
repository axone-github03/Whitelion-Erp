<style>



	.highlight-cart-btn {

  -webkit-animation: glowing 1500ms infinite;
  -moz-animation: glowing 1500ms infinite;
  -o-animation: glowing 1500ms infinite;
  animation: glowing 1500ms infinite;
}


@-webkit-keyframes glowing {
  0% { background-color: #0059b2; -webkit-box-shadow: 0 0 3px #0059b2; }
  50% { background-color: #0095ff; -webkit-box-shadow: 0 0 40px #0095ff; }
  100% { background-color: #0059b2; -webkit-box-shadow: 0 0 3px #0059b2; }
}

@-moz-keyframes glowing {
  0% { background-color: #0059b2; -moz-box-shadow: 0 0 3px #0059b2; }
  50% { background-color: #0095ff; -moz-box-shadow: 0 0 40px #0095ff; }
  100% { background-color: #0059b2; -moz-box-shadow: 0 0 3px #0059b2; }
}

@-o-keyframes glowing {
  0% { background-color: #0059b2; box-shadow: 0 0 3px #0059b2; }
  50% { background-color: #0095ff; box-shadow: 0 0 40px #0095ff; }
  100% { background-color: #0059b2; box-shadow: 0 0 3px #0059b2; }
}

@keyframes glowing {
  0% { background-color: #0059b2; box-shadow: 0 0 3px #0059b2; }
  50% { background-color: #0095ff; box-shadow: 0 0 40px #0095ff; }
  100% { background-color: #0059b2; box-shadow: 0 0 3px #0059b2; }
}


</style>
<button type="button" class="btn btn-success waves-effect btn-label waves-light"><i class="bx bx-trophy label-icon"></i> Total Points : {{$data['point_data']->total_point_current }}</button>


 <a id="CartBtnLable"  href="{{route('architect.gift.products.cart')}}" class="btn btn-primary waves-effect waves-light"  role="button"> <i class="bx bx-cart font-size-16 align-middle me-2" ></i>Cart (<span id="cartItemCount">0</span>)</a>