<script>
 var el = $.map(MacrosArr, function(val, i) {
  return "<option>" + val + "</option>";
});

$("#macros_select_box").html('<option value="">...</option>'+el.join(""));
</script>
<select id="macros_select_box" ></select>
