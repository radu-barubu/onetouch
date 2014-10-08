<?php

class PreferencesDisplay extends AppModel 
{ 
	public $name = 'PreferencesDisplay'; 
	public $primaryKey = 'preferences_display_id';
	public $useTable = 'preferences_display';
	public $default_accepted_contrast = 300;
	public $max_contrast = 750;
	public $fields_contrast = array(
		'editable_hightlight_color' => 50,
		'button_border_color' => 90,
		'button_hover_border_color' => 90,
		'button_background_color_from' => 0,
		'button_background_color_to' => 0,
		'button_text_color' => 450,
		'top_menu_font_color' => 500,
		'body_font_color' => 550
	);
	
	public function get_all_field_contrast()
	{
		return $this->fields_contrast;
	}
	
	public function get_field_contrast($field_name)
	{
		if(isset($this->fields_contrast[$field_name]))
		{
			return $this->fields_contrast[$field_name];
		}
		else
		{
			return $this->default_accepted_contrast;
		}
	}
	
	public function is_contrast_accepted($forecolor, $backgroundcolor, $accepted_contrast_from, $accepted_contrast_to)
	{
		$forecolor = str_replace("#", "", $forecolor);
		$backgroundcolor = str_replace("#", "", $backgroundcolor);
		
		$R1 = hexdec(substr($forecolor, 0, 2));
		$G1 = hexdec(substr($forecolor, 2, 2));
		$B1 = hexdec(substr($forecolor, 4, 2));
		
		$R2 = hexdec(substr($backgroundcolor, 0, 2));
		$G2 = hexdec(substr($backgroundcolor, 2, 2));
		$B2 = hexdec(substr($backgroundcolor, 4, 2));
		
		$diff = max($R1, $R2) - min($R1, $R2) + max($G1, $G2) - min($G1, $G2) + max($B1, $B2) - min($B1, $B2);
		
		if($diff >= $accepted_contrast_from && $diff <= $accepted_contrast_to)
		{
			return true;
		}
		
		return false;
	}
	
	private function getDefaultValue($field)
	{
		return $this->_schema[$field]['default'];
	}
	
	private function searchItem($user_id)
	{
		$search_result = $this->find(
				'first', 
				array(
					'conditions' => array('PreferencesDisplay.user_id' => $user_id)
				)
		);
		
		if(!empty($search_result))
		{
			return $search_result;
		}
		else
		{
			return false;
		}
	}
	
	public function getFontList()
	{
		return array('Arial', 'Arial Narrow', 'Comic Sans MS', 'Lucida Console', 'Palatino Linotype', 'Tahoma', 'Times New Roman', 'Verdana');
	}
	
	public function getSchemeProperties($specific = "")
	{
		$scheme_data = array(
			array(
				'scheme' => 'black', 
				'img' => '1A1A1A.png',
				'background' => '#eeeeee',
				'nav_container' => '#1a1a1a',
				'header' => '#3c3c3c', 
				'header_border_bottom' => '#707070',
				'nav_ul_li_hover' => '#2b2b2b',
				'nav_ul_li_ul_li_hover' => '#000000',
				'listing_border' => '#dfdfdf',
				'listing_tr_th' => '#e5e5e5',
				'table_stripped' => '#F3F3F3',
				'field_border_color' => '#AAAAAA',
				'tab_bg' => '#e6e6e6'
			),
			array(
				'scheme' => 'green', 
				'img' => '003300.png',
				'background' => '#EAFFEA',
				'nav_container' => '#002400',
				'header' => '#003E00',
				'header_border_bottom' => '#007500',
				'nav_ul_li_hover' => '#003000',
				'nav_ul_li_ul_li_hover' => '#002000',
				'listing_border' => '#DADFC6',
				'listing_tr_th' => '#E3EEC1',
				'table_stripped' => '#F2F8E4',
				'field_border_color' => '#b4c08c',
				'tab_bg' => '#D6E7AD'
			),
			array(
				'scheme' => 'blue', 
				'img' => '003366.png',
				'background' => '#DDEEFF',
				'nav_container' => '#001A33',
				'header' => '#00376F', 
				'header_border_bottom' => '#0058B0',
				'nav_ul_li_hover' => '#002448',
				'nav_ul_li_ul_li_hover' => '#001224',
				'listing_border' => '#CAE4FF',
				'listing_tr_th' => '#DDEEFF',
				'table_stripped' => '#F4FAFF',
				'field_border_color' => '#A4BED4',
				'tab_bg' => '#D7EBF9'
			),
			array(
				'scheme' => 'brown', 
				'img' => '993300.png',
				'background' => '#FFE8DD',
				'nav_container' => '#481700',
				'header' => '#882D00', 
				'header_border_bottom' => '#BF3F00',
				'nav_ul_li_hover' => '#622000',
				'nav_ul_li_ul_li_hover' => '#2F0F00',
				'listing_border' => '#FFD5BF',
				'listing_tr_th' => '#FFE3D5',
				'table_stripped' => '#FFF8F4',
				'field_border_color' => '#E0CFC2',
				'tab_bg' => '#EDE4D4'
			),
			array(
				'scheme' => 'red', 
				'img' => 'CC0000.png',
				'background' => '#FFECEC',
				'nav_container' => '#640000',
				'header' => '#CC0033', 
				'header_border_bottom' => '#FF1A53',
				'nav_ul_li_hover' => '#820000',
				'nav_ul_li_ul_li_hover' => '#550000',
				'listing_border' => '#FFD7D7',
				'listing_tr_th' => '#FFDFDF',
				'table_stripped' => '#FFF0F0',
				'field_border_color' => '#E27B89',
				'tab_bg' => '#F6D8DC'
			)
		);
		
		if(strlen($specific) == 0)
		{
			return $scheme_data;
		}
		else
		{
			foreach($scheme_data as $scheme)
			{
				if($scheme['scheme'] == $specific)
				{
					return $scheme;
				}
			}
		}
	}
	
	public function saveDefault($data)
	{
		foreach($data['PreferencesDisplay'] as $key => $value)
		{
			if($key == 'preferences_display_id' || $key == 'modified_timestamp' || $key == 'modified_user_id')
			{
				continue;
			}
			
			$data['PreferencesDisplay'][$key] = $this->getDefaultValue($key);
		}
		
		pr($data);
		
		$data['PreferencesDisplay']['modified_timestamp'] = __date("Y-m-d H:i:s");
		$this->save($data);
	}

	public function getDisplaySettings($user_id)
	{
		$search_result = $this->searchItem($user_id);
		
		if($search_result)
		{
			$ret = $search_result['PreferencesDisplay'];
			$ret['color_scheme_properties'] = $this->getSchemeProperties($ret['color_scheme']);
			return $ret;
		}
		else
		{
			$this->create();
			$data = array();
			$data['PreferencesDisplay']['user_id'] = $user_id;
			$this->data['PreferencesDisplay']['modified_timestamp'] = __date("Y-m-d H:i:s");
			$this->data['PreferencesDisplay']['modified_user_id'] = $user_id;
			
			if($this->save($data))
			{
				return $this->getDisplaySettings($user_id);
			}
		}
	}
}

?>