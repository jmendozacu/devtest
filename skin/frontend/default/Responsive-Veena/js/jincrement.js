jQuery(document).ready(function(){
    var $j = jQuery.noConflict();
    $j("div.quantity").append('<input type="button" value="+" id="add1" class="plus" />').prepend('<input type="button" value="-" id="minus1" class="minus" />');
    $j(".plus").click(function(){
        var currentVal = parseInt($j(this).prev(".qty").val());
        if (!currentVal || currentVal=="" || currentVal == "NaN") 
           currentVal = 0;
           $j(this).prev(".qty").val(currentVal + 1);
    });
    $j(".minus").click(function(){
        var currentVal = parseInt($j(this).next(".qty").val());
        if (currentVal == "NaN") 
            currentVal = 0;
            if (currentVal > 0){
                $j(this).next(".qty").val(currentVal - 1);
            }
    });
});