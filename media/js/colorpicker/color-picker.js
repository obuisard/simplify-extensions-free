/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license	GNU General Public License version 3 or later; see LICENSE.txt
 */
class ColourPicker extends HTMLElement {
  constructor() {
    super()

    this.swatch = ''
    this.input = ''
    this.panel = ''
  }

  connectedCallback() {

    // Create swatch
    this.swatch = document.createElement('colour-swatch')

    // Create panel
    this.panel = document.createElement('div')
    this.panel.classList.add('panel')

    // Append the elements
    this.appendChild(this.swatch)
    this.appendChild(this.panel)

    this.init()
  }
  
  init() {
    this.input = this.querySelector('input[type=text]');

    const defaultColour = this.input.value !== '' ? this.input.value : '#fff';
    const colorSpace = this.input.getAttribute('data-color-format') !== null ? this.input.getAttribute('data-color-format') : 'hex';
	
	let picker = null;

	if (colorSpace == 'rgba' || colorSpace == 'hex8') {
		this.querySelector('.panel').style.width = '230px';
		
	    picker = new iro.ColorPicker(this.panel, {
	      width: 150,
	      layoutDirection: 'horizontal',
	      color: defaultColour,
	      layout: [
			{
		      component: iro.ui.Box,
		    },
			{
		      component: iro.ui.Slider,
		      options: {
		        id: 'hue-slider',
		        sliderType: 'hue'
		      }
		    },
		    {
		      component: iro.ui.Slider,
		      options: {
		        sliderType: 'alpha'
		      }
		    },
		  ]
	    })
	} else if (colorSpace == 'rgb' || colorSpace == 'hex') {
		picker = new iro.ColorPicker(this.panel, {
	      width: 150,
	      layoutDirection: 'horizontal',
	      color: defaultColour,
	      layout: [
		    {
		      component: iro.ui.Box,
		    },
		    {
		      component: iro.ui.Slider,
		      options: {
		        id: 'hue-slider',
		        sliderType: 'hue'
		      }
		    }
		  ]
	    })
	} else if (colorSpace == 'hsla') {
		this.querySelector('.panel').style.width = '230px';
		
	    picker = new iro.ColorPicker(this.panel, {
	      width: 150,
	      layoutDirection: 'horizontal',
	      color: defaultColour,
	      layout: [
			{
		      component: iro.ui.Wheel,
		    },
			{
		      component: iro.ui.Slider,
		      options: {
		        sliderType: 'value'
		      }
		    },
		    {
		      component: iro.ui.Slider,
		      options: {
		        sliderType: 'alpha'
		      }
		    },
		  ]
	    })
	} else {
		picker = new iro.ColorPicker(this.panel, {
	      width: 150,
	      layoutDirection: 'horizontal',
	      color: defaultColour
	    })
	}

    picker.on(['color:change'], (color) => {
      
      switch(colorSpace) {
			case 'rgb': this.input.value = color.rgbString; this.swatch.style.backgroundColor = color.rgbString; break;
			case 'rgba': this.input.value = color.rgbaString; this.swatch.style.backgroundColor = color.rgbaString; break;
			case 'hsl': this.input.value = color.hslString; this.swatch.style.backgroundColor = color.hslString; break;
			case 'hsla': this.input.value = color.hslaString; this.swatch.style.backgroundColor = color.hslaString; break;
			case 'hex8': this.input.value = color.hex8String; this.swatch.style.backgroundColor = color.hex8String; break;
			default: this.input.value = color.hexString; this.swatch.style.backgroundColor = color.hexString;
		}
      
      this.input.dispatchEvent(new Event('input', { bubbles: true }))
    })

    picker.on(['color:init'], (color) => {
      this.swatch.style.backgroundColor = this.input.value
    })

    this.input.addEventListener('change', ({ target }) => {
      
		switch(colorSpace) {
			case 'rgb': picker.color.rgbString = target.value; break;
			case 'rgba': picker.color.rgbaString = target.value; break;
			case 'hsl': picker.color.hslString = target.value; break;
			case 'hsla': picker.color.hslaString = target.value; break;
			case 'hex8': picker.color.hex8String = target.value; break;
			default: picker.color.hexString = target.value;
		}
    })

    // If the swatch is clicked, trigger a focus on the input to open the panel
    this.swatch.addEventListener('click', () => {
      this.input.focus()
    })

    this.input.addEventListener('focus', () => {
      if (!this.panel.classList.contains('is-open')) {
        this.panel.classList.add('is-open')
      }
    })

    this.input.addEventListener('blur', () => {
      this.panel.classList.remove('is-open')
    })
  }
}

// Define the new element
customElements.define('colour-picker', ColourPicker)
