<?php


/**
 * FormBuilder Class
 * 
 * Handles processing of form-definition format strings
 * 
 */
class FormBuilder {

	/**
	 *
	 * @var string HTML code of the generated form 
	 */
	private $form = '';

	/**
	 *
	 * @var array Template definition for current form 
	 */
	private $template = array();
	
	/**
	 *
	 * @var string Form name
	 */
	private $name;
	
	/**
	 *
	 * @var array Array containing field information 
	 */
	private $fields = array();
	
	/**
	 *
	 * @var array Array containing Models and actions
	 * with corresonding field names that will be passed
	 * to the model callbacks when saving
	 */
	private $saveCallbacks = array();
	
	/**
	 *
	 * @var array Array containing Models and actions
	 * with corresonding field names that will be passed
	 * to the model callbacks when saving
	 */
	private $loadCallbacks = array();
	
	
	/**
	 *
	 * @var boolean Flag to denote to generate form markup suitable for PDF printing 
	 */
	public $pdfVersion = false;

	/**
	 *
	 * @var boolean Flag to determine whether to use default/data provided when printing pdf
	 */
	public $pdfData = false;
  
	/**
	 *
	 * @var boolean Flag to determine if equivalent json format will be included
	 */
	public $withJSON = false;
	
	/**
	 * Generates the entire HTML form markup 
	 * from a given form definition
	 * 
	 * @param mixed $template JSON string or PHP array representing form definition
	 * @param mixed $data JSON string or PHP array representing form data
	 * @return string Generate HTML code for given form-definition
	 */
	public function build($template, $data = array()) {
		$this->form = '';
		$this->fields = array();
		$this->template = array();
		
		$template = $this->toArray($template);
		$data = $this->toArray($data);
		
		
		if ( !$template ) {
			return '';
		}

		$this->template = $template;
		
		$components = '';

		// Render components
		if ( isset($template['components']) ) {
			$components .= $this->renderComponents($template['components'], $data);
		}
		
		$form = '
				%s
    ';
		$this->form = sprintf($form, 
			$components
		);
		
		return $this->form;
	}

	/**
	 * Goes through the list for form components
	 * and renders appropriate component type (snippet or form element)
	 * 
	 * @param array $componentsList
	 * @param mixed $data JSON string or PHP array representing form data
	 * @return string HTML code of all the form components
	 */
	public function renderComponents($componentsList, $data = array()) {
		$components = '';

		// Empty list returns blank
		if ( !$componentsList ) {
			return $components;
		}
		
		$data = $this->toArray($data);

		foreach ( $componentsList as $c ) {

			// Add snippet type component
			if ( isset($c['type']) && $c['type'] == 'snippet' ) {
				$components .= $this->renderSnippet($c);
			}

			// Add element type component
			if ( isset($c['type']) && $c['type'] == 'element' ) {
				$components .= $this->renderElement($c, $data);
			}
		}

		return $components;
	}

	/**
	 * Generate snippet HTML markup
	 * based on given definition
	 * 
	 * @param array $snippetData Array definition of the snippet$elementData['component']
	 * @return string HTML code for snippet
	 */
	public function renderSnippet($snippetData) {
		$snippet = '';

		if ( !$snippetData ) {
			return $snippet;
		}

		if ( isset($snippetData['component']['content']) ) {
			$snippet = $snippetData['component']['content'];
		}

		if ($this->withJSON) {
			$snippetData['type'] = 'snippet';
			$snippet = '<div class="form-component-snippet" data-json_format="'.htmlentities(json_encode($snippetData)).'">' .$snippet.'</div>';
    } else {
      $snippet = '<div class="form-component-snippet">'. $snippet .'</div>';
    }
		
		
		return $snippet;
	}

	/**
	 * Generate appropriate HTML form element
	 * based on definition
	 * 
	 * @param array $elementData Element definition array
	 * @param mixed $data JSON string or PHP array representing form data
	 * @return string  HTML code for element
	 */
	public function renderElement($elementData, $data = array()) {
		if ( !$elementData ) {
			return '';
		}

		$validTypes = array('hidden', 'text', 'textarea', 'select', 'radio', 'checkbox', 'signature');


		$type = isset($elementData['component']['type']) ? strtolower($elementData['component']['type']) : 'text';

		if ( !in_array($type, $validTypes) ) {
			return '';
		}
		
		$data = $this->toArray($data);

		// If form data was given, check if element name exists
		// and has equivalent value in data
		if (
			!empty($data) && 
			isset($elementData['component']['name']) && 
			array_key_exists($elementData['component']['name'], $data)
			) {
			

			$elementData['component']['default'] = $data[$elementData['component']['name']];
			
			// If not an array
			if (!is_array($data[$elementData['component']['name']])) {
				// Use data value as default
				// coerce to string
				$elementData['component']['default'] = '' .$data[$elementData['component']['name']];
			}
			
		}

		if ($type == 'signature') {
			if (isset($data[$elementData['component']['name'].'_background'])) {
				$elementData['component']['background'] = $data[$elementData['component']['name'].'_background'];
			}
			
			
		}
		
		$type .= 'Element';

		$element = $this->$type($elementData['component']);

		if ( $element == '' ) {
			return $element;
		}

		$this->fields[$elementData['component']['name']] = $elementData['component'];
		
		$this->registerCallbacks($elementData);
		
		if ($type === 'hiddenElement') {
			return $element;
		}
		
		$json = '';
		if ($this->withJSON) {
			$json = ' data-json_format="' .htmlentities(json_encode(array_merge($elementData, array('type' => 'element')))) .'" ';
		}
		
		$wrap = '
			<div %s class="form-component-element %s" '.$json.' style="%s">
				%s
			</div>';

    $elementData['height'] = (isset($elementData['height'])) ? trim($elementData['height']) : '';
    $elementData['width'] = (isset($elementData['width'])) ? trim($elementData['width']) : '';
    
    $dimensions = '';
    
    foreach (array('height', 'width') as $dim) {
      $unit = 'px';

      $index = stripos($elementData[$dim], 'px');
      if ($index !== false) {
        $unit = 'px';
        $elementData[$dim] = intval(substr($elementData[$dim], 0, strlen($elementData[$dim]) - $index + 1));
      }

      $index = stripos($elementData[$dim], '%');
      if ($index !== false) {
        $unit = '%';
        $elementData[$dim] = intval(substr($elementData[$dim], 0, strlen($elementData[$dim]) - $index + 1));
      }

      if ($elementData[$dim]) {
        $dimensions .= ' ' . $dim . ': ' . $elementData[$dim] . $unit . '; ';
      }
    }
    
		$elementData['id'] = (isset($elementData['id'])) ? trim($elementData['id']) : '';
		$elementData['class'] = (isset($elementData['class'])) ? trim($elementData['class']) : '';

		return
			sprintf($wrap, ($elementData['id']) ? ' id="' . htmlentities($elementData['id']) . '" ' : '', ($elementData['class']) ? htmlentities($elementData['class']) : '', $dimensions, $element
		);
	}

	/**
	 * Generate text field element
	 * @param array $elementData Element definition
	 * @return string HTML code for text field element 
	 */
	public function hiddenElement($elementData) {
		$element = '';

		if ( !$elementData ) {
			return $element;
		}

		$element = '
			<input type="hidden" name="%s" value="%s" %s %s/>
		';


		$elementData['name'] = (isset($elementData['name'])) ? trim($elementData['name']) : 'name';
		$elementData['default'] = (isset($elementData['default'])) ? trim($elementData['default']) : '';
		$elementData['id'] = (isset($elementData['id'])) ? trim($elementData['id']) : '';
		$elementData['class'] = (isset($elementData['class'])) ? trim($elementData['class']) : '';



		$element = sprintf(
			$element, 
			($elementData['name']) ? htmlentities($elementData['name']) : 'name', 
			($elementData['default']) ? htmlentities($elementData['default']) : '', 
			($elementData['id']) ? ' id="' . htmlentities($elementData['default']) . '" ' : '', 
			($elementData['class']) ? ' class="' . htmlentities($elementData['class']) . '" ' : ''
		);

		return $element;
	}
	
	/**
	 * Generate text field element
	 * @param array $elementData Element definition
	 * @return string HTML code for text field element 
	 */
	public function textElement($elementData) {
		$element = '';

		if ( !$elementData ) {
			return $element;
		}

		$element = '
				<label %s>%s %s</label>
			<input type="text" name="%s" value="%s" %s %s %s/> %s
		';


		$elementData['label'] = (isset($elementData['label'])) ? trim($elementData['label']) : 'Label';
		$elementData['suffix'] = (isset($elementData['suffix'])) ? trim($elementData['suffix']) : '';
		$elementData['name'] = (isset($elementData['name'])) ? trim($elementData['name']) : 'name';
		$elementData['size'] = (isset($elementData['size'])) ? intval(trim($elementData['size'])) : 100;
    $elementData['size'] = ($elementData['size']) ? $elementData['size'] : 100;
    
		$elementData['default'] = (isset($elementData['default'])) ? trim($elementData['default']) : '';
		$elementData['id'] = (isset($elementData['id'])) ? trim($elementData['id']) : '';
		$elementData['class'] = (isset($elementData['class'])) ? trim($elementData['class']) : '';
		$elementData['required'] = (isset($elementData['required'])) ? $elementData['required'] : false;

		if ( $elementData['required'] ) {
			$elementData['class'] .= ' required ';
		}

		if ($this->pdfVersion){
      
      if (!$this->pdfData) {
        $elementData['default'] = '';
        $element = '
            <label %s>%s %s</label>
            <br />
            <br />
            __________________________________________________
						<br />
						%s
        ';			
        
				$element = sprintf(
					$element, 
					($elementData['id']) ? ' for ="' . htmlentities($elementData['id']) . '" ' : '', 
					($elementData['label']) ? ($elementData['label']) : 'Label', 
					($elementData['required']) ? '<span class="asterisk">*</span>' : '', 
					$elementData['suffix']
				);
        
				
				
      } else {
        $elementData['name'] = ' ';
        $element = '
            <label %s>%s %s</label>
            <br />
            <br />
            %s %s %s
        ';			

				$element = sprintf(
					$element, 
					($elementData['id']) ? ' for ="' . htmlentities($elementData['id']) . '" ' : '', 
					($elementData['label']) ? ($elementData['label']) : 'Label', 
					($elementData['required']) ? '<span class="asterisk">*</span>' : '', 
					($elementData['name']) ? htmlentities($elementData['name']) : 'name', 
					($elementData['default']) ? htmlentities($elementData['default']) : '', 
					$elementData['suffix']
				);
				
				
        
      }
			
		} else {
			$element = sprintf(
				$element, ($elementData['id']) ? ' for ="' . htmlentities($elementData['id']) . '" ' : '', ($elementData['label']) ? ($elementData['label']) : 'Label', ($elementData['required']) ? '<span class="asterisk">*</span>' : '', ($elementData['name']) ? htmlentities($elementData['name']) : 'name', ($elementData['default']) ? htmlentities($elementData['default']) : '', ($elementData['id']) ? ' id="' . htmlentities($elementData['default']) . '" ' : '', ($elementData['class']) ? ' class="' . htmlentities($elementData['class']) . '" ' : '',
							' style="width: '.$elementData['size'].'px" ', $elementData['suffix']
			);
			
		}
		
		return $element;
	}

	/**
	 * Generate textarea element
	 * @param array $elementData Element definition
	 * @return string HTML code for textarea field element 
	 */
	public function textareaElement($elementData) {
		
		if ($this->pdfVersion) {
			return $this->textElement($elementData);
		}
		
		$element = '';

		if ( !$elementData ) {
			return $element;
		}

		$element = '
				<label %s>%s %s</label>
				<textarea name="%s" %s %s rows="%s" cols="%s">%s</textarea> %s
		';


		$elementData['label'] = (isset($elementData['label'])) ? trim($elementData['label']) : 'Label';
		$elementData['suffix'] = (isset($elementData['suffix'])) ? trim($elementData['suffix']) : '';
		$elementData['name'] = (isset($elementData['name'])) ? trim($elementData['name']) : 'name';
		$elementData['default'] = (isset($elementData['default'])) ? trim($elementData['default']) : '';
		$elementData['id'] = (isset($elementData['id'])) ? trim($elementData['id']) : '';
		$elementData['class'] = (isset($elementData['class'])) ? trim($elementData['class']) : '';
		$elementData['required'] = (isset($elementData['required'])) ? $elementData['required'] : false;
		$elementData['rows'] = (isset($elementData['rows'])) ? intval($elementData['rows']) : 3;
		$elementData['cols'] = (isset($elementData['cols'])) ? intval($elementData['cols']) : 30;

		if ( $elementData['required'] ) {
			$elementData['class'] .= ' required ';
		}

		if ($this->pdfVersion){
      
      if (!$this->pdfData) {
        $elementData['default'] = '';
      }
      
			
			$element = '
					<label %s>%s %s</label>
					<br />
					<textarea name="%s" %s %s rows="%s" cols="%s">%s</textarea> %s
			';			
		}
		
		$element = sprintf(
			$element, ($elementData['id']) ? ' for ="' . htmlentities($elementData['id']) . '" ' : '', ($elementData['label']) ? ($elementData['label']) : 'Label', ($elementData['required']) ? '<span class="asterisk">*</span>' : '', ($elementData['name']) ? htmlentities($elementData['name']) : 'name', ($elementData['id']) ? ' id="' . htmlentities($elementData['default']) . '" ' : '', ($elementData['class']) ? ' class="' . htmlentities($elementData['class']) . '" ' : '', ($elementData['rows']) ? $elementData['rows'] : 3, ($elementData['cols']) ? $elementData['cols'] : 30, 
				($elementData['default']) ? htmlentities($elementData['default']) : '', $elementData['suffix']
		);

		return $element;
	}

	/**
	 * Generate select/dropdown field element
	 * @param array $elementData Element definition
	 * @return string HTML code for select field element 
	 */
	public function selectElement($elementData) {
		$element = '';

		if ( !$elementData ) {
			return $element;
		}

		// Abort if no options were given
		if ( !isset($elementData['elementOptions']) || !$elementData['elementOptions'] ) {
			return '';
		}


    // Process default selected options, if any
    $default = array();
    if ( isset($elementData['default']) ) {

      // Force into an array if single default value given
      if ( !is_array($elementData['default']) ) {
        $elementData['default'] = array($elementData['default']);
      }

      // Note default values (cast to string type)
      foreach ( $elementData['default'] as $d ) {
        $val = trim($d . '');

        // Only include non-empty values
        if ( $val != '') {
          $default[] = $val;
        }
      }
    }    
    
		if ($this->pdfVersion) {
			$element = '
					<label>%s %s</label>
					<br />
					<table>
					%s
					</table>
					%s
			';

			$optionTemplate = '
				<tr>
					<td"><div class="pdf-select-box %s">&nbsp;</div></td>
					<td>%s</td>
				</tr>
			';

			$elementData['label'] = (isset($elementData['label'])) ? trim($elementData['label']) : 'Label';
			$elementData['suffix'] = (isset($elementData['suffix'])) ? trim($elementData['suffix']) : '';
			$elementData['name'] = (isset($elementData['name'])) ? trim($elementData['name']) : '';
			$elementData['id'] = (isset($elementData['id'])) ? trim($elementData['id']) : '';
			$elementData['class'] = (isset($elementData['class'])) ? trim($elementData['class']) : '';
			$elementData['required'] = (isset($elementData['required'])) ? $elementData['required'] : false;
			$elementData['multiple'] = (isset($elementData['multiple'])) ? $elementData['multiple'] : false;

			if ( $elementData['required'] ) {
				$elementData['class'] .= ' required ';
			}

			// Process options
			$options = '';
			foreach ( $elementData['elementOptions'] as $o ) {
				$value = isset($o['value']) ? trim($o['value'] . '') : '';

				// Skip Empty value
				if ( $value == '' ) {
					continue;
				}
				// Use value for label if no label is given
				$label = (isset($o['label'])) ? trim($o['label']) : $value;

        
        if (!$this->pdfData) {
          $options .= sprintf(
            $optionTemplate, '', $label
          );
        } else {
          if ((in_array($value, $default))) {
            $options .= sprintf(
              $optionTemplate, 'selected', '<u>'.$label .'</u>'
            );
            
          } else {
            $options .= sprintf(
              $optionTemplate, '', $label
            );
            
          }
        }
			}

			$name = ($elementData['name']) ? htmlentities($elementData['name']) : 'name';

			if ($elementData['multiple']) {
				$name .= '[]';
			}

			$element = sprintf(
				$element, ($elementData['label']) ? ($elementData['label']) : 'Label', ($elementData['required']) ? '<span class="asterisk">*</span>' : '',  $options,
				$elementData['suffix']
			);			
			
			
		} else {
			$element = '
					<label %s>%s %s</label>
					<select name="%s" %s %s>
					%s
					</select>
					%s
			';

			$optionTemplate = '
				<option value="%s" %s >%s</option>
			';

			$elementData['label'] = (isset($elementData['label'])) ? trim($elementData['label']) : 'Label';
			$elementData['suffix'] = (isset($elementData['suffix'])) ? trim($elementData['suffix']) : '';
			$elementData['name'] = (isset($elementData['name'])) ? trim($elementData['name']) : 'name';
			$elementData['id'] = (isset($elementData['id'])) ? trim($elementData['id']) : '';
			$elementData['class'] = (isset($elementData['class'])) ? trim($elementData['class']) : '';
			$elementData['required'] = (isset($elementData['required'])) ? $elementData['required'] : false;
			$elementData['multiple'] = (isset($elementData['multiple'])) ? $elementData['multiple'] : false;

			if ( $elementData['required'] ) {
				$elementData['class'] .= ' required ';
			}




			// Process options
			$options = '';
			foreach ( $elementData['elementOptions'] as $o ) {
				$value = isset($o['value']) ? trim($o['value'] . '') : '';

				// Skip Empty value
				if ( $value == '' ) {
					continue;
				}
				// Use value for label if no label is given
				$label = (isset($o['label'])) ? trim($o['label']) : $value;

				$options .= sprintf(
					$optionTemplate, $value, (in_array($value, $default)) ? ' selected="selected" ' : '', $label
				);
			}

			$name = ($elementData['name']) ? htmlentities($elementData['name']) : 'name';

			if ($elementData['multiple']) {
				$name .= '[]';
			}

			$element = sprintf(
				$element, ($elementData['id']) ? ' for ="' . htmlentities($elementData['id']) . '" ' : '', ($elementData['label']) ? htmlentities($elementData['label']) : 'Label', ($elementData['required']) ? '<span class="asterisk">*</span>' : '', $name, ($elementData['id']) ? ' id="' . htmlentities($elementData['default']) . '" ' : '', ($elementData['class']) ? ' class="' . htmlentities($elementData['class']) . '" ' : '', $options,
				$elementData['suffix']
			);			
		}

		return $element;
	}

	/**
	 * Generate radio set element
	 * @param array $elementData Element definition
	 * @return string HTML code for radio set 
	 */
	public function radioElement($elementData) {
		
		if ($this->pdfVersion) {
			return $this->selectElement($elementData);
		}
		
		$element = '';

		if ( !$elementData ) {
			return $element;
		}

		// Abort if no options were given
		if ( !isset($elementData['elementOptions']) || !$elementData['elementOptions'] ) {
			return '';
		}

		$element = '
				<span>%s %s</span>
				<div %s class="form-radio-wrap %s">
					%s
				</div>
				%s
		';

		$radioTemplate = '
			<input type="radio" id="%s" name="%s" value="%s" %s ><label for="%s">%s</label>
		';

		$elementData['label'] = (isset($elementData['label'])) ? trim($elementData['label']) : 'Label';
		$elementData['suffix'] = (isset($elementData['suffix'])) ? trim($elementData['suffix']) : '';
		$elementData['name'] = (isset($elementData['name'])) ? trim($elementData['name']) : 'name';
		$elementData['id'] = (isset($elementData['id'])) ? trim($elementData['id']) : '';
		$elementData['class'] = (isset($elementData['class'])) ? trim($elementData['class']) : '';
		$elementData['required'] = (isset($elementData['required'])) ? $elementData['required'] : false;

		if ( $elementData['required'] ) {
			$elementData['class'] .= ' required ';
		}

		// Process default selected options, if any
		$default = array();
		if ( isset($elementData['default']) ) {

			// Force into an array if single default value given
			if ( !is_array($elementData['default']) ) {
				$elementData['default'] = array($elementData['default']);
			}

			// Note default values (cast to string type)
			foreach ( $elementData['default'] as $d ) {
				$val = trim($d . '');

				// Only include non-empty values
				if ( $val != '') {
					$default[] = $val;
				}
			}
		}


		// Process options
		$options = '';
		$name = htmlentities($elementData['name']);

		$ct = 0;
		foreach ( $elementData['elementOptions'] as $o ) {
			$value = isset($o['value']) ? trim($o['value'] . '') : '';

			// Skip Empty value
			if ( $value == '' ) {
				continue;
			}
			// Use value for label if no label is given
			$label = (isset($o['label'])) ? trim($o['label']) : $value;

			$id = $name . '_' . $ct++;
			$options .= sprintf(
				$radioTemplate, $id, $name, $value, (in_array($value, $default)) ? ' checked="checked" ' : '', $id, ($label)
			);
		}

		$element = sprintf(
			$element, ($elementData['label']) ? ($elementData['label']) : 'Label', ($elementData['required']) ? '<span class="asterisk">*</span>' : '', ($elementData['id']) ? ' id="' . htmlentities($elementData['default']) . '" ' : '', ($elementData['class']) ? ' class="' . htmlentities($elementData['class']) . '" ' : '', $options,
			$elementData['suffix']
		);

		return $element;
	}

	/**
	 * Generate checkbox set element
	 * @param array $elementData Element definition
	 * @return string HTML code for checkbox set 
	 */
	public function checkboxElement($elementData) {
		if ($this->pdfVersion) {
			return $this->selectElement($elementData);
		}		
		
		$element = '';

		if ( !$elementData ) {
			return $element;
		}

		// Abort if no options were given
		if ( !isset($elementData['elementOptions']) || !$elementData['elementOptions'] ) {
			return '';
		}


		$element = '
				<span>%s %s</span>
				<div %s class="form-checkbox-wrap %s">
					%s
				</div>
				%s
		';

		$chekboxTemplate = '
			<label for="%s" class="label_check_box"><input type="checkbox" id="%s" name="%s[]" value="%s" %s /> %s </label>
		';

		$elementData['label'] = (isset($elementData['label'])) ? trim($elementData['label']) : 'Label';
		$elementData['suffix'] = (isset($elementData['suffix'])) ? trim($elementData['suffix']) : '';
		$elementData['name'] = (isset($elementData['name'])) ? trim($elementData['name']) : 'name';
		$elementData['id'] = (isset($elementData['id'])) ? trim($elementData['id']) : '';
		$elementData['class'] = (isset($elementData['class'])) ? trim($elementData['class']) : '';
		$elementData['required'] = (isset($elementData['required'])) ? $elementData['required'] : false;

		if ( $elementData['required'] ) {
			$elementData['class'] .= ' required ';
		}

		// Process default selected options, if any
		$default = array();
		if ( isset($elementData['default']) ) {

			// Force into an array if single default value given
			if ( !is_array($elementData['default']) ) {
				$elementData['default'] = array($elementData['default']);
			}

			// Note default values (cast to string type)
			foreach ( $elementData['default'] as $d ) {
				$val = trim($d . '');

				// Only include non-empty values
				if ( $val != '') {
					$default[] = $val;
				}
			}
		}


		// Process options
		$options = '';
		$name = htmlentities($elementData['name']);

		$ct = 0;
		foreach ( $elementData['elementOptions'] as $o ) {
			$value = isset($o['value']) ? trim($o['value'] . '') : '';

			// Skip Empty value
			if ( $value == '' ) {
				continue;
			}
			// Use value for label if no label is given
			$label = (isset($o['label'])) ? trim($o['label']) : $value;

			$id = $name . '_' . $ct++;
			$options .= sprintf(
				$chekboxTemplate, $id, $id, $name, $value, (in_array($value, $default)) ? ' checked="checked" ' : '', ($label)
			);
		}

		$element = sprintf(
			$element, ($elementData['label']) ? ($elementData['label']) : 'Label', ($elementData['required']) ? '<span class="asterisk">*</span>' : '', ($elementData['id']) ? ' id="' . htmlentities($elementData['default']) . '" ' : '', ($elementData['class']) ? ' class="' . htmlentities($elementData['class']) . '" ' : '', $options,
			$elementData['suffix']
		);

		return $element;
	}
	
	/**
	 * Generate checkbox set element
	 * @param array $elementData Element definition
	 * @return string HTML code for checkbox set 
	 */
	public function signatureElement($elementData) {
		$role_id=$_SESSION['UserAccount']['role_id'];
		$bkground_btn=($role_id == EMR_Roles::SYSTEM_ADMIN_ROLE_ID|| $role_id == EMR_Roles::PRACTICE_ADMIN_ROLE_ID) ? true:false;

		$element = '';

		if ( !$elementData ) {
			return $element;
		}

		if ($this->pdfVersion) {
			return '
			<div class="signature">
			
				__________________________________________________
				<br />
				Signature Over Printed Name
			</div>	
			';
		}
		
		$element = '
			<div>
				<div name="%s" class="form_signature"></div>
				<input type="hidden" name="%s" value="%s" %s %s/>
				<input type="hidden" name="%s_background" id="%s_background" value="%s" class="background-img"/>
				<br class="clear" style="margin-bottom: 1em;"/>
				<div class="signature-opts">
					<input type="button" value="Clear Drawing/Text" class="btn clear_signature" />

							<div style="position: relative; width: 214px; height: auto !important; z-index: 999999">
									<div style="position: absolute; top: 1px; margin-left: 150px; width: 214px;" removeonread="true">
											<div style="position: relative;"> 
';
if($bkground_btn)
$element .= '<span class="btn" style="float: left; margin-top: -2px; padding-top: 4px; padding-bottom: 4px; z-index: 99999;">Change backgound image</span>											<div style="position: absolute; top: 0px; left: 0px;">
	<input type="file" id="%s_image" name="%s_image" class="img-upload" value=""/>
	</div>';

$element .='
											</div>
									</div>
							</div>
					<input type="button" value="Remove Image" class="btn remove_image" style="position: relative; left: 230px;" />
				</div>

			</div>
		';


		$elementData['name'] = (isset($elementData['name'])) ? trim($elementData['name']) : 'name';
		$elementData['default'] = (isset($elementData['default'])) ? trim($elementData['default']) : '';
		$elementData['id'] = (isset($elementData['id'])) ? trim($elementData['id']) : '';
		$elementData['class'] = (isset($elementData['class'])) ? trim($elementData['class']) : '';
		$elementData['background'] = (isset($elementData['background'])) ? trim($elementData['background']) : '';

		
		$element = sprintf(
			$element, 
			($elementData['name']) ? htmlentities($elementData['name']) : 'name', 
			($elementData['name']) ? htmlentities($elementData['name']) : 'name', 
			($elementData['default']) ? htmlentities($elementData['default']) : '', 
			($elementData['id']) ? ' id="' . htmlentities($elementData['id']) . '" ' : '', 
			($elementData['class']) ? ' class="' . htmlentities($elementData['class']) . '" ' : '',
			($elementData['name']), 
			($elementData['name']), 
			$elementData['background'], 
			($elementData['name']), 
			($elementData['name'])
		);

		return $element;		
	}
	
	/**
	 * Convert given form template
	 * from JSON format to PHP array
	 * 
	 * @param mixed $template JSON string or PHP array representing form definition
	 * @return array Array defining form template 
	 */
	public function toArray($template) {
		// Empty output if not data was provided
		if ( !$template ) {
			return array();
		}

		// Convert to PHP array if data is a JSON string
		if ( !is_array($template) ) {
			$template = json_decode($template, true);
		}
		
		return $template;
	}
	
	/**
	 * Get name of fields defined by the form
	 * 
	 * @param mixed $template JSON string or PHP array representing form definition
	 * @return array Array containing names of the fields 
	 */
	public function extractFields($template) {
		$fields = array();
		$template = $this->toArray($template);
		
		if( ! isset($template['components']) || empty($template['components'])){
			return $fields;
		}
		
		foreach ($template['components'] as $c) {
			if ($c['type'] != 'element') {
				continue;
			}
			
			$name = isset($c['component']['name']) ? trim($c['component']['name']) : '';
			
			if (!$name) {
				continue;
			}
			
			$fields[] = $name;
			
			if ($c['component']['type'] == 'signature') {
				$fields[] = $name . '_background';
			}
			
		}
		
		return $fields;
		
	}
	
	/**
	 * Inspect and determine if template passed
	 * is well-formed and valid
	 * 
	 * 
	 * @return boolean True if ok. False. otherwise
	 */
	public function checkTemplate($template) {
		
		return true;
	}
	
	/**
	 * Build JSON data from the POSTed data
	 * based on template given
	 * 
	 * @param mixed $template JSON string or PHP array representing form definition
	 * @param type $post
	 * @return string JSON data string 
	 */
	public function extractData($template, $post = array()) {
		$data = '';
		
		$template = $this->toArray($template);

		$fields = $this->extractFields($template);
		
		if (!$fields) {
			return $data;
		}
		
		$data = array();
		
		foreach ($fields as $f) {
			if (isset($post[$f])) {
				$data[$f] = $post[$f];
			}
		}
		
		return json_encode($data);
	}
	

	public function getSampleForms() {
		
		
		
		$form1 = <<<EOS
{
	"components": [
	{
		"type": "snippet",
		"component": 	{
			"content": "<h1>Sample Form Heading</h1>"
		}
	},

	{
		"type": "element",
		"class": "two-column",
		"component": {
			"name" : "hidden_sample",
			"type": "hidden"
		},
		"save": {
			"PatientDemographic": ["templateFunction1", "templateFunction3"],
			"EncounterMaster": "templateFunction1"
		},
		"load": {
			"UserAccount": "currentUser"
		}

	},
				
	{
		"type": "element",
		"class": "two-column",
		"component": {
			"name" : "text_sample",
			"label": "Sample Text field",
			"type": "text",
			"default": "Enter text here",
			"required": true
		},
		"save": {
			"PatientDemographic": ["templateFunction1", "templateFunction3"],
			"EncounterMaster": "templateFunction1"
		}
	},
				
	{
		"type": "element",
		"class": "two-column",
		"component": {
			"name" : "textarea_sample",
			"label": "Text Area sample",
			"type": "textarea",
			"default": "text here",
			"required": false
		},
		"save": {
			"PatientDemographic": ["templateFunction1", "templateFunction2"],
			"EncounterMaster": "templateFunction1"
		}
	},

	{
		"type": "snippet",
		"component": 	{
			"content": "<br class=\"clear\" />"
		}
	},

	{
		"type": "element",
		"class": "three-column",
		"component": {
			"name" : "select_sample",
			"label": "Select field sample",
			"type": "select",
			"default": "1",
			"elementOptions": [
			{
				"label": "Option 1",
				"value":"1"
			},

			{
				"label": "Option 2",
				"value": "2"
			},

			{
				"label": "Option 3",
				"value": "3"
			}
			]
		},
		"save": {
			"ScheduleCalendar": ["templateFunction"]
		}
	},
				
	{
		"type": "element",
		"class": "three-column",
		"component": {
			"name" : "radio_sample",
			"label": "Your Alert Preference",
			"type": "radio",
			"default": "2",
			"elementOptions": 
			[
			{
				"label": "Phone",
				"value": "1"
			},

			{
				"label": "Email",
				"value": "0"
			},

			{
				"label": "SMS",
				"value": "2"
			}
			]
		},
		"load": {
			"UserAccount": ["loadUserSettings"]
		},
		"save": {
			"UserAccount": ["saveUserSettings"]
		}
	},

	{
		"type": "element",
		"class": "three-column",
		"component":
		{
			"name" : "checkbox_sample",
			"label":"Checkbox fields sample",
			"type": "checkbox",
			"default": ["1", "3"],
			"elementOptions": 
			[
			{
				"label":"Option 1",
				"value": "1"
			},

			{
				"label": "Option 2",
				"value": "2"
			},

			{
				"label": "Option 3",
				"value": "3"
			}
			]
		}
	},

	{
		"type": "snippet",
		"component": 	{
			"content": "<br class=\"clear\" />"
		}
	},


	{
		"type": "element",
		"component":
		{
			"name" : "signature_sample",
			"label": "signature sample",
			"type": "signature"
			
		}
	},			
				
				
	{
		"type": "snippet",
		"component": 	{
			"content": "<br class=\"clear\" />"
		}
	}

	],
	"data": null
}
EOS;
		
$form2 = <<<EOS
{
    "components" : [

	{
		"type": "snippet",
		"component": 	{
			"content": "<h1>Questionnaire Demo</h1>"
		}
	},

	{
		"type": "element",
		"component": {
			"name" : "surgery_history",
			"label": "1) Have you ever had surgery ?",
			"type": "radio",
			"default": "0",
			"elementOptions": 
			[
			{
				"label": "Yes",
				"value": "1"
			},

			{
				"label": "No",
				"value": "0"
			}
			]
		}
	},

	{
		"type": "snippet",
		"component": 	{
			"content": "<p>2) have you had any of the following conditions?</p>"
		}
	},

        {
		"type": "element",
		"component": {
			"name" : "diabetes",
			"label": "Diabetes",
			"type": "radio",
			"default": "0",
			"elementOptions": 
			[
			{
				"label": "Yes",
				"value": "1"
			},

			{
				"label": "No",
				"value": "0"
			}
			]
		}
	},

        {
		"type": "element",
		"component": {
			"name" : "hypertension",
			"label": "Hypertension",
			"type": "radio",
			"default": "0",
			"elementOptions": 
			[
			{
				"label": "Yes",
				"value": "1"
			},

			{
				"label": "No",
				"value": "0"
			}
			]
		}
	},

	{
		"type": "element",
		"component": {
			"name" : "pain_rating",
			"label": "3) Rate your Pain (In a scale of 1-10)",
			"type": "select",
			"default": "1",
			"elementOptions": [
			{
				"value":"1"
			},

			{
				"value": "2"
			},

			{
				"value": "3"
			},

			{
				"value": "4"
			},

			{
				"value": "5"
			},

			{
				"value": "6"
			},

			{
				"value": "7"
			},

			{
				"value": "8"
			},

			{
				"value": "9"
			},

			{
				"value": "10"
			}
			]
		}
	},

	{
		"type": "snippet",
		"component": 	{
			"content": "<p>Please provide your signature below:</p> <br />"
		}
	},

	{
		"type": "element",
		"component":
		{
			"name" : "signature_sample",
			"label": "signature sample",
			"type": "signature"
			
		}
	}

    ]

}		
		
EOS;
		
		return array(
			'Sample Form' => $form1,
			'Questionnaire' => $form2,
		);
		
	}

	/**
	 * Groups callbacks and fields to be passed to the callbacks
	 * 
	 * @param array $component Array containing field component info
	 */
	private function registerCallbacks($component) {
		// Save Callbacks
		if (isset($component['save'])) {
				foreach ($component['save'] as $model => $methodList) {
					
					if (!isset($this->saveCallbacks[$model])) {
						$this->saveCallbacks[$model] = array();
					}
					
					if (!is_array($methodList)) {
						$methodList = array($methodList);
					}
					
					foreach ($methodList as $method) {
						if (!isset($this->saveCallbacks[$model][$method])) {
							$this->saveCallbacks[$model][$method] = array();
						}
						
						$this->saveCallbacks[$model][$method][] = $component['component']['name'];
					}
					
				}
		}
		
			// Load Callbacks
		if (isset($component['load'])) {
				foreach ($component['load'] as $model => $methodList) {
					
					if (!isset($this->loadCallbacks[$model])) {
						$this->loadCallbacks[$model] = array();
					}
					
					if (!is_array($methodList)) {
						$methodList = array($methodList);
					}
					
					foreach ($methodList as $method) {
						if (!isset($this->loadCallbacks[$model][$method])) {
							$this->loadCallbacks[$model][$method] = array();
						}
						
						$this->loadCallbacks[$model][$method][] = $component['component']['name'];
					}
					
				}
		}	
		
	}

	public function getSaveCallback() {
		print_r($this->saveCallbacks);
	}
	
	
	/**
	 * 
	 * Triggers database-related save operations
	 * as specified in the template
	 * 
	 * @param mixed $template JSON string or PHP array representing form definition
	 * @param mixed $data JSON string or PHP array representing form data
	 */
	public function triggerSave($template, $data = array()) {
			$this->build($template);
		$data = $this->toArray($data);
		
		foreach ($this->saveCallbacks as $model => $methodList) {
			App::import('Model', $model);
			
			if (!class_exists($model)) {
				continue;
			}
			
			$currentModel = new $model();
		
			foreach ($methodList as $method => $fieldNames) {
				if (!method_exists($currentModel, $method)) {
					continue;
				}
				
				$parameters = array();
				
				foreach ($fieldNames as $name) {
					if (!isset($data[$name])) {
						continue;
					}
					
					$parameters[$name] = $data[$name];
				}
				
				$currentModel->$method($parameters);
				
			}
		}
	}

	/**
	 * 
	 * Triggers database-related load operations
	 * as specified in the template
	 * 
	 * @param mixed $template JSON string or PHP array representing form definition
	 * @param mixed $data JSON string or PHP array representing form data
	 * @return string JSON data string containing values loaded from database
	 */
	public function triggerLoad($template = false, $data = array()) {

		// If there was a template parameter passed
		// rebuild the current form template
		if ($template !== false) {
			$this->build($template);
		}

		$data = $this->toArray($data);
		
		$loaded = array();
		
		foreach ($this->loadCallbacks as $model => $methodList) {
			App::import('Model', $model);
			
			if (!class_exists($model)) {
				continue;
			}
			
			$currentModel = new $model();
			
			foreach ($methodList as $method => $fieldNames) {
				if (!method_exists($currentModel, $method)) {
					continue;
				}
				
				$parameters = array();
				
				foreach ($fieldNames as $name) {
					if (!isset($data[$name])) {
						continue;
					}
					
					$parameters[$name] = $data[$name];
				}
				
				$loaded = array_merge($loaded, $currentModel->$method($parameters));
			}
		}
		
		return json_encode($loaded);		
	}
	
	
	public function getDataMap($template, $data, $opts = array()) {
		
		$defaultOpts = array(
			'preserve_columns' => false
		);
		
		$opts = array_merge($defaultOpts, $opts);
		
		$map = array();
		$template = $this->toArray($template);
		$data = $this->toArray($data);
		
		
		if ( !$template ) {
			return $map;
		}		
		
		if (!isset($template['components'])) {
			return $map;
		}
		
		$lastClass = false;		
		$row = array();
		
		foreach ($template['components'] as $c ) {

			if (!isset($c['type'])) {
				continue;
			}
			
			if ($c['type'] == 'snippet' ) {
				/*
				if (stristr($c['component']['content'], 'signature')) {
					continue;
				}
				*/
				$map[] = array(
					'question' => '',
					'answer' => '',
					'snippet' => $c['component']['content'],
				);
				
				continue;
			}

			if ($c['component']['type'] == 'signature') {
				continue;
			}

			if ($c['component']['type'] == 'hidden') {
				continue;
			}			
			
			$answer = $data[$c['component']['name']];

			if (in_array($c['component']['type'], array('radio', 'select', 'checkbox'))) {
				$tmpAnswer = array();
				foreach ($c['component']['elementOptions'] as $opt) {
					
					if (is_array($answer)) {
						if (in_array($opt['value'].'', $answer)) {


							$tmpAnswer[] = (isset($opt['label'])) ?  $opt['label']: $opt['value'];
						}
					} else {
						if (($answer .'') == ($opt['value'].'') ) {


							$answer = (isset($opt['label'])) ?  $opt['label']: $opt['value'];
							break;
						}
					}
				}
				
				if ($tmpAnswer) {
					$answer = implode(', ', $tmpAnswer);
				}
				
			}
			
			
			if ($opts['preserve_columns']) {
				$columnClass = isset($c['class']) ? $c['class'] : false;
				
				if ($columnClass == 'single-column') {
					$columnClass = false;
				}
				
				if ($columnClass === false || $columnClass != $lastClass) {
					
					if ($row) {
						$map[] = $row;
						$row = array();
					}
					
					if ($columnClass !== false) {
						$lastClass = $columnClass;
					}
					
				} 
				
				$row[] = array(
					'question' => strip_tags($c['component']['label']),
					'answer' => $answer,
					'suffix' => isset($c['component']['suffix']) ? $c['component']['suffix'] : '',
				);
				
				
			} else {
				$map[] = array(
					'question' => strip_tags($c['component']['label']),
					'answer' => $answer,
					'suffix' => isset($c['component']['suffix']) ? $c['component']['suffix'] : '',
				);
				
			}
			
			
			
		}
		
		if ($row) {
			$map[] = $row;
		}

		return $map;
	}
	
	
	
}
