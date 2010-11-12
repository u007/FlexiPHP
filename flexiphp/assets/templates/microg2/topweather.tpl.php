<?php

$aCountry = array(
	"2388929" => "Dallas, United States",
  "2459115" => "New York, United States",
	"2373280" => "California, United States",
	"2514815" => "Washington DC, United States",
	"44418" 	=> "London, Great Britain",
  "554890"  => "Copenhagen, Denmark",
  "727232"  => "Amsterdam, Netherland",
  "721943"  => "Rome, Italy",
  "615702"  => "Paris, Franch",
  "1225448" => "Bangkok, Thailand",
	"1118370" => "Tokyo, Japan",
	"1154781" => "Kuala Lumpur, Malaysia",
  "2306179" => "Taipei, Taiwan",
	"2165352" => "Hong Kong, China",
	"1062617" => "Singapore",
	"2161838" => "Guangzhou, China",
	"2151849" => "Shanghai, China",
	"2151330" => "Beijing, China",
  "1199477" => "Manila, Philippines",
  "1047378" => "Jakarta, Indonesia",
  "1020725" => "Bandar Seri Begawan, Brunei"
);

$mSelected = FlexiController::getInstance()->getQuery("formWeatherCity","");

if (empty($mSelected))
{
	$mSelected  = FlexiController::getInstance()->getSession("savedWeatherCity", "1154781");
}
else
{
	FlexiController::getInstance()->setSession("savedWeatherCity", $mSelected);
}

$aSelectWeather = array("onChange" => "switchWeather('formWeatherCity');");

?>
<script type="text/javascript">
	function switchWeather(sSelect)
	{
		//console.log(oSelect.items[oSelect.selectedIndex]);
		//GetWeather
		var cityId = $("#formWeather #" + sSelect).val();
		$.ajax({
			type: "GET",
			data: "cityid=" + cityId,
				url: '<?=$this->url(null,"getWeather", "yahooweather")?>',
				success: function(data) {
				$('#divshowweather').html(data);
				}
		});
	}
	
	$(document).ready(function() {
		switchWeather('formWeatherCity');
	});
</script>
<form id="formWeather" name="formWeather">
	<div id="divshowweather" style="height: 50px; margin-top: -15px; margin-bottom: 5px;">
		
	</div>
	<div class="clear"></div>
	<!--http://weather.yahooapis.com/forecastrss?p=MYXX0008&u=f-->
	<div>
		<span>Show weather for: </span>
		<?=$this->renderFormSelectRaw("formWeatherCity", $mSelected, $aCountry, "formWeatherCity", $aSelectWeather); ?>
	</div>
</form>
