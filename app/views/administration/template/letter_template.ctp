<?php
switch($location['state'])
{
    case "Alabama" : $location['state'] = "AL"; break;
    case "Alaska" : $location['state'] = "AK"; break;
    case "Arizona" : $location['state'] = "AZ"; break;
    case "Arkansas" : $location['state'] = "AR"; break;
    case "California" : $location['state'] = "CA"; break;
    case "Colorado" : $location['state'] = "CO"; break;
    case "Connecticut" : $location['state'] = "CT"; break;
    case "Delaware" : $location['state'] = "DE"; break;
    case "District Of Columbia" : $location['state'] = "DC"; break;
    case "Florida" : $location['state'] = "FL"; break;
    case "Georgia" : $location['state'] = "GA"; break;
    case "Hawaii" : $location['state'] = "HI"; break;
    case "Idaho" : $location['state'] = "ID"; break;
    case "Illinois" : $location['state'] = "IL"; break;
    case "Indiana" : $location['state'] = "IN"; break;
    case "Iowa" : $location['state'] = "IA"; break;
    case "Kansas" : $location['state'] = "KS"; break;
    case "Kentucky" : $location['state'] = "KY"; break;
    case "Louisiana" : $location['state'] = "LA"; break;
    case "Maine" : $location['state'] = "ME"; break;
    case "Maryland" : $location['state'] = "MD"; break;
    case "Massachusetts" : $location['state'] = "MA"; break;
    case "Michigan" : $location['state'] = "MI"; break;
    case "Minnesota" : $location['state'] = "MN"; break;
    case "Mississippi" : $location['state'] = "MS"; break;
    case "Missouri" : $location['state'] = "MO"; break;
    case "Montana" : $location['state'] = "MT"; break;
    case "Nebraska" : $location['state'] = "NE"; break;
    case "Nevada" : $location['state'] = "NV"; break;
    case "New Hampshire" : $location['state'] = "NH"; break;
    case "New Jersey" : $location['state'] = "NJ"; break;
    case "New Mexico" : $location['state'] = "NM"; break;
    case "New York" : $location['state'] = "NY"; break;
    case "North Carolina" : $location['state'] = "NC"; break;
    case "North Dakota" : $location['state'] = "ND"; break;
    case "Ohio" : $location['state'] = "OH"; break;
    case "Oklahoma" : $location['state'] = "OK"; break;
    case "Oregon" : $location['state'] = "or"; break;
    case "Pennsylvania" : $location['state'] = "PA"; break;
    case "Rhode Island" : $location['state'] = "RI"; break;
    case "South Carolina" : $location['state'] = "SC"; break;
    case "South Dakota" : $location['state'] = "SD"; break;
    case "Tennessee" : $location['state'] = "TN"; break;
    case "Texas" : $location['state'] = "TX"; break;
    case "Utah" : $location['state'] = "UT"; break;
    case "Vermont" : $location['state'] = "VT"; break;
    case "Virginia" : $location['state'] = "VA"; break;
    case "Washington" : $location['state'] = "WA"; break;
    case "West Virginia" : $location['state'] = "WV"; break;
    case "Wisconsin" : $location['state'] = "WI"; break;
    case "Wyoming" : $location['state'] = "WY"; break;
}
$logo_char = Router::url("/", true);
//Removing the extra character using substr
$logo_char_remove = substr($logo_char, 0, -1); 
$image_logo = $logo_char_remove.$url_rel_paths['administration'].$practice_profile;



$http = 'http://';
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
    || $_SERVER['SERVER_PORT'] == 443) {

    $http = 'https://';
}

$adminUrl = $http . $_SERVER['SERVER_NAME'] . $url_abs_paths['administration'];


$template_data["content"] = preg_replace('/\[image\:([0-9a-zA-Z_.]*)]/i', '<img src="'. $adminUrl .'$1" />', $template_data["content"]);




?>
<html>
<head>
    <title></title>
    <style>
	body {
		line-height: 1;	
		margin: -23px 40px 0px 40px;
    
    <?php if (in_array($template_data["font"], LetterTemplate::$fonts)): ?> 
    font-family: '<?php echo $template_data["font"] ?>' !important;
    <?php endif;?>
    
	}
	</style>
</head>
<body>
    <div>
    	<?php if($template_data["use_practice_logo"] == 1 && $template_data['logo_position'] != ''): ?>
            <p align="<?php echo $template_data['logo_position']; ?>"><img src="<?php echo $image_logo; ?>"  height="auto" width = "auto" /></p>
        <?php endif; ?> 
        
        <?php if($template_data["use_practice_address"] == 1 && $template_data['address_position'] == 'top'): ?>
            <p align="center"><?php echo $location["address_line_1"].', '.$location["city"].', '.$location["state"].' '.$location["zip"]; ?></p>
        <?php endif; ?>
        
        <p><?php echo nl2br($template_data["content"]); ?></p>
        
        <?php if($template_data["use_practice_address"] == 1 && $template_data['address_position'] == 'bottom'): ?>
            <div style="position: absolute; bottom: -23px;"><p align="center"><?php echo $location["address_line_1"].', '.$location["city"].', '.$location["state"].' '.$location["zip"]; ?></p></div>
        <?php endif; ?>
    </div>
</body>
</html>