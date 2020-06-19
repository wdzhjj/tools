<script>
	//获取鼠标当前点击的对象
	$("li").click(function(event) {
	  var $target = $(event.target);
	  alert($target.text());
      // var id_val = target.attr("id");  获取对象id

	});



</script>