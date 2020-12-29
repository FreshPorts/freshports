<ul id="allItems">
     <li id="1" class="Cat1">Item 1</li>
     <li id="2" class="Cat1">Item 2</li>
     <li id="3" class="Cat2" style="display:none;">C2Item 1</li>
     <li id="4" class="Cat2" style="display:none;">C2Item 2</li>
     <li id="5" class="Cat3" style="display:none;">C3Item 1</li>
 </ul>
 
<a href="#" onclick="allItemsDisplay('allitems'); return false;">Expand this list</a>

 
 
<script type="text/javascript">
 
 function allItemsDisplay(thsElem,thsVal){
  $('#' + thsElem).children().css('display','none');
  $('#' + thsElem).children('.' + thsVal).css('display','');
}
 
function XXXallItemsDisplay(thsVal){
  var $theseLI = $('#allItems').children();
  $theseLI.css('display','none');
  $theseLI.find('.' + thsVal).css('display','');
}
</script>



<ul id="myList">
<li>Item 1</li>
<li>Item 2</li>
<li>Item 3</li>
<li>Item 4</li>
<li>Item 5</li>
<li>Item 6</li>
<li>Item 7</li>
<li>Item 8</li>
<li>Item 9</li>
<li>Item 10</li>
</ul>


<script type="text/javascript">
 
function HideThatStuff(){

var list = $('#myList li:gt(4)');
list.hide();
$('a#myList-toggle').click(function() {
list.slideToggle(400);
return false;
});

}
</script>

<a href="#" onclick="HideThatStuff(); return false;">Expand this list</a>




<ol id="myList" style="margin-bottom: 0px">
    <li>Item 1</li>
    <li>Item 2</li>
    <li>Item 3</li>
    <li>Item 4</li>
    <li>Item 5</li>
</ol>
<ol id="myListExt" style="margin-top: 0px" start="6">
    <li>Item 6</li>
    <li>Item 7</li>
    <li>Item 8</li>
    <li>Item 9</li>
    <li>Item 10</li>
</ol>