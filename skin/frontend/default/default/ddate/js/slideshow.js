var currentSlide = 1;
var contentSlides = "";
var totalSlides;
  
jQuery(document).ready(function(){
    jQuery("#slideshow-previous").click(showPreviousSlide);
    jQuery("#slideshow-next").click(showNextSlide);
  
    var totalWidth = 0;
    totalSlides = 0;
    contentSlides = jQuery(".slideshow-content");
    contentSlides.each(function(i){
        totalWidth += this.clientWidth;
        totalSlides++;
    });
    jQuery("#slideshow-holder").width(500*totalSlides);
    jQuery("#slideshow-scroller").attr({
        scrollLeft: 0
    });
    updateButtons();
});
//document.observe('dom:loaded', function(){
//    $('slideshow-previous').observe('click', showPreviousSlide());
//    $('slideshow-next').observe('click', showNextSlide());
//    
//    var totalWidth = 0;
//    
//    contentSlides = $$(".slideshow-content");
//    totalSlides = contentSlides.size();
//    $("slideshow-holder").writeAttribute('width',500*totalSlides);
//    $("slideshow-scroller").writeAttribute('scrollLeft', 0);
//    updateButtons();
//});

function showPreviousSlide()
{
    currentSlide--;
    updateContentHolder();
    updateButtons();
}

function showNextSlide()
{
    currentSlide++;
    updateContentHolder();
    updateButtons();
}

function updateContentHolder()
{
    var scrollAmount = 0;
    contentSlides.each(function(i){
        if(currentSlide - 1 > i) {
            scrollAmount += this.clientWidth;
        }
    });
    jQuery("#slideshow-scroller").animate({
        scrollLeft: scrollAmount
    }, 500);
}
//function updateContentHolder()
//{
//    var scrollAmount = 0;
//    contentSlides.each(function(i){
//        if(currentSlide - 1 > i) {
//            scrollAmount += this.clientWidth;
//        }
//    });
//    
////    Effect.ScrollTo($('slideshow-scroller'), );
//}

function updateButtons()
{
    if(currentSlide < totalSlides) {
        jQuery("#slideshow-next").show();
    } else {
        jQuery("#slideshow-next").hide();
    }
    if(currentSlide > 1) {
        jQuery("#slideshow-previous").show();
    } else {
        jQuery("#slideshow-previous").hide();
    }
}

if(document.getElementById("min_date")!=null){
    var a = 0;
    jQuery("#ddate-trigger-picker").click(function(){
        if(a==0){
            document.getElementById("cont").style.display="block";
            a=1;
        }else{
            document.getElementById("cont").style.display="none";
            a=0;
        }
    });


    var min_date = parseInt(document.getElementById("min_date").value);
    var max_date = parseInt(document.getElementById("max_date").value);
    var d_saturday = parseInt(document.getElementById("d_saturday").value);
    var d_sunday = parseInt(document.getElementById("d_sunday").value);
    var special_day = document.getElementById("special_day").value;
    special_day = special_day.split(';');
    var DISABLE_DATES = new Array();
    var j=0;
    for(i=0;i<special_day.length;i++){
        if(special_day[i]!=""){
            var spt = special_day[i].split('-');
            DISABLE_DATES[j] = spt[0] + spt[1] + spt[2] + ":true";
            j++;
        }
    }
	
    var LEFT_CAL = Calendar.setup({
        cont: "cont",
        weekNumbers: true,
        selectionType: Calendar.SEL_MULTIPLE,
        showTime: 12,
        min: min_date,
        max: max_date,
        weekNumbers:true,
        disabled : function(date) {
            if(((date.getDay() == 6) && (d_saturday =="0")) || 
                ((date.getDay() == 0) && (d_sunday =="0"))){ 
                return true;
            }else{
                date = Calendar.dateToInt(date);
                return date in DISABLE_DATES;
	
            }
        }  
    });
	
    LEFT_CAL.addEventListener("onSelect", function(){
        var ta = document.getElementById("delivery_date");
        ta.value = this.selection.print("%Y-%m-%d %p").join("\n");
        document.getElementById("ddate:date").value = this.selection.print("%Y-%m-%d");
        document.getElementById("ddate:dtime").value = this.selection.print("%p");
	              
    });
}