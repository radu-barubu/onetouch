

var sample = 
{
	"name": "Sample Form",
	"submitText": "Save Data",
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
;
