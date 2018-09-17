/*jslint undef: true, browser: true, continue: true, eqeq: true, vars: true, forin: true, white: true, newcap: false, nomen: true, plusplus: true, maxerr: 50, indent: 4 */

/**
 * jGestures: a jQuery plugin for gesture events
 * Copyright 2010-2011 Neue Digitale / Razorfish GmbH
 * Copyright 2011-2012, Razorfish GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @fileOverview
 * Razorfish GmbH javascript library: add touch events such as 'pinch',
 * 'rotate', 'swipe', 'tap' and 'orientationchange' on capable user agents.
 * For incapable devices there's a basic event substitution: a "tapone" event
 * can be triggered by "clicking", a "swipeone" by performing a swipe-ish
 * gesture using the mouse (buttondown - mousemove - buttonup).
 *
 * This is still a beta version, bugfixes and improvements appreciated.
 *
 * @author martin.krause@razorfish.de
 * @version 0.90-shake
 *
 * @requires
 * jQuery JavaScript Library v1.4.2 - http://jquery.com/
 *	Copyright 2010, John Resig
 *	Dual licensed under the MIT or GPL Version 2 licenses.
 *	http://jquery.org/license
 *
 * @example	jQuery('#swipe').bind('swipeone',eventHandler);
 *
 * Notification on native events:
 * On every native touchstart, touchend, gesturestart and gestureend-event,
 * jgestures triggers a corresponding custom event
 * ('jGestures.touchstart', 'jGestures.touchend;start', 'jGestures.touchend;processed',
 * 'jGestures.gesturestart', 'jGestures.gestureend;start', 'jGestures.gestureend;processed') on the event-element.
 * The  eventhandler's second argument represents the original touch event (yes: including all touchpoints).
 * Use this if you need very detailed control e.g. kinetic scrolling or implementing additional gestures.
 *
 * Every jGesture-eventhandler receives a custom object as second argument
 * containing the original event (originalEvent property) and processed
 * information (such as delta values and timesptamp).
 * Example:{
 *				type: eventtype e.g. "swipe","pinch",
 *				originalEvent: {DOM-Event},
 *				// default: just one entry on the delta-array - the first touchpoint
 *				// the first touchpoint is the reference point for every gesture,
 *				// because moving touchpoints in various directions would result in
 *				// a gesture.
 *				// delta and direction details are just provided for touch not for gesture / motion events
 *				delta : [
 *					{
 *						lastX:{Number} , // x-axis: relative to the last touchevent (e.g. touchmove!)
 *						lastY:{Number}, // y-axis: relative to the last touchevent (e.g. touchmove!)
 *						moved: {Number},  // distance: relative to the original touchpoint
 *						startX: {Number} , // relative to the original touchpoint
 *						startY: {Number} ,// relative to the original touchpoint
 *					} ],
 *				// based on the first touchpoint
 *				direction : { // relative to the last touchevent (e.g. touchmove!)
 *					vector: {Number}, // -1|+1, indicates the direction if necessary(pinch/rotate)
 *					orientation: {Number} // window.orientation: -90,0,90,180 || null (window.orienntation)
 *					lastX : {Number}, // -1,0,+1 || null (orientationchange) // relative to the last touchevent (e.g. touchmove!)
 *					lastY : {Number}, // -1,0,+1 || null (orientationchange)// relative to the last touchevent (e.g. touchmove!)
 *					startX: {Number} , // relative to the original touchpoint
 *					startY: {Number} ,// relative to the original touchpoint
 *				},
 *				rotation: {Number} || null, // gestureonly: amount of rotation relative to the current position NOT the original
 *				scale: {Number} || null, // gestureonly: amount of scaling relative to the current position NOT the original
 *				duration: {Number}, // ms: relative to the original touchpoint
 *				description : {String} // details as String: {TYPE *}:{TOUCHES 1|2|3|4}:{X-AXIS 'right'|'left'|'steady'}:{Y-AXIS 'down'|'up'|'steady'} e.g. "swipe:1:left:steady" relative to the last touchpoint
 *			};
 *
 * Available jGesture-events can be grouped into:
 *
 *
 * Device events:
 *	The jGesture-Events in this group are triggered by the device.
 *
 * @event 'orientationchange'
 *		The device is turned clockwise or counterclockwise. This event is triggered
 *		by the device and might use an internal gyroscope.
 *	obj.description:
 *		orientationchange:landscape:clockwise:-90
 *		orientationchange:portrait:default:0
 *		orientationchange:landscape:counterclockwise|portrait:90
 *		orientationchange:portrait:upsidedown:180
 *
 *
 * Move events:
 *	The jGesture-Events in this group are triggered during the touch/gesture
 *	execution whenever a touchpoint changes.
 *	In contrast to touchend/gestureend-events which are triggered after
 *	the touch/gesture has completed.
 *
 * @event 'pinch'
 *		Is triggered during a pinch gesture (two fingers moving away from or
 *		towards each other).
 *	obj.description:
 *		pinch:-1:close
 *		pinch:+1:open
 *
 * @event 'rotate'
 *		Is triggered during a rotation gesture (two fingers rotating clockwise
 *		or counterclockwise).
 *	obj.description:
 *		rotate:-1:counterclockwise
 *		rotate:+1:+clockwise
 *
 * @event 'swipemove'
 *		Is triggered during a swipe move gesture (finger(s) being moved around
 *		the device, e.g. dragging)
 *	obj.description:
 *		swipemove:1:left:down
 *		swipemove:1:left:up
 *		swipemove:1:left:steady
 *		swipemove:1:right:down
 *		swipemove:1:right:up
 *		swipemove:1:right:steady
 *		swipemove:2:left:down
 *		swipemove:2:left:up
 *		swipemove:2:left:steady
 *		swipemove:2:right:down
 *		swipemove:2:right:up
 *		swipemove:2:right:steady
 *		swipemove:2:left:down
 *		swipemove:3:left:up
 *		swipemove:3:left:steady
 *		swipemove:3:right:down
 *		swipemove:3:right:up
 *		swipemove:3:right:steady
 *		swipemove:3:left:down
 *		swipemove:4:left:up
 *		swipemove:4:left:steady
 *		swipemove:4:right:down
 *		swipemove:4:right:up
 *		swipemove:4:right:steady
 *
 *
 * Toucheend events:
 *	The jGesture-Events in this group are triggered after the touch/gesture
 *	has completed.
 *	In contrast to touchmove-events which are triggered during the touch/gesture
 *	execution whenever a touchpoint changes.
 *
 * @event 'swipeone'
 *		Is triggered after a swipe move gesture with one touchpoint (one finger
 *		was moved around the device)
 *	obj.description:
 *		swipeone:1:left:down
 *		swipeone:1:left:up
 *		swipeone:1:left:steady
 *		swipeone:1:right:down
 *		swipeone:1:right:up
 *		swipeone:1:right:steady
 *
 * @event 'swipetwo'
 *		Is triggered after a swipe move gesture with two touchpoints (two fingers
 *		were moved around the device)
 *	obj.description:
 *		swipetwo:2:left:down
 *		swipetwo:2:left:up
 *		swipetwo:2:left:steady
 *		swipetwo:2:right:down
 *		swipetwo:2:right:up
 *		swipetwo:2:right:steady
 *
 * @event 'swipethree'
 *		Is triggered after a swipe move gesture with three touchpoints (three
 *		fingers were moved around the device)
 *	obj.description:
 *		swipethree:3:left:down
 *		swipethree:3:left:up
 *		swipethree:3:left:steady
 *		swipethree:3:right:down
 *		swipethree:3:right:up
 *		swipethree:3:right:steady
 *
 * @event 'swipefour'
 *		Is triggered after a swipe move gesture with four touchpoints (four
 *		fingers were moved around the device)
 *	obj.description:
 *		swipefour:4:left:down
 *		swipefour:4:left:up
 *		swipefour:4:left:steady
 *		swipefour:4:right:down
 *		swipefour:4:right:up
 *		swipefour:4:right:steady
 *
 *
 * @event 'swipeup'
 *		Is triggered after an  strict upwards swipe move gesture
 *	obj.description:
 *		swipe:1:steady:up
 *		swipe:2:steady:up
 *		swipe:3:steady:up
 *		swipe:4:steady:up
 *
 * @event 'swiperightup'
 *		Is triggered after a rightwards and upwards swipe move gesture
 *	obj.description:
 *		swipe:1:right:up
 *		swipe:2:right:up
 *		swipe:3:right:up
 *		swipe:4:right:up
 *
 * @event 'swiperight'
 *		Is triggered after a  strict rightwards swipe move gesture
 *	obj.description:
 *		swipe:1:right:steady
 *		swipe:2:right:steady
 *		swipe:3:right:steady
 *		swipe:4:right:steady
 *
 * @event 'swiperightdown'
 *		Is triggered after a rightwards and downwards swipe move gesture
 *	obj.description:
 *		swipe:1:right:down
 *		swipe:2:right:down
 *		swipe:3:right:down
 *		swipe:4:right:down
 *
 * @event 'swipedown'
 *		Is triggered after a  strict downwards swipe move gesture
 *	obj.description:
 *		swipe:1:steady:down
 *		swipe:2:steady:down
 *		swipe:3:steady:down
 *		swipe:4:steady:down
 *
 * @event 'swipeleftdown'
 *		Is triggered after a leftwards and downwards swipe move gesture
 *	obj.description:
 *		swipe:1:left:down
 *		swipe:2:left:down
 *		swipe:3:left:down
 *		swipe:4:left:down
 *
 * @event 'swipeleft'
 *		Is triggered after a strict leftwards swipe move gesture
 *	obj.description:
 *		swipe:1:left:steady
 *		swipe:2:left:steady
 *		swipe:3:left:steady
 *		swipe:4:left:steady
 *
 * @event 'swipeleftup'
 *		Is triggered after a leftwards and upwards swipe move gesture
 *	obj.description:
 *		swipe:1:left:up
 *		swipe:2:left:up
 *		swipe:3:left:up
 *		swipe:4:left:up
 *
 * @event 'tapone'
 *		Is triggered after a single (one finger) tap gesture
 *	obj.description:
 *		tapone
 *
 * @event 'taptwo'
 *		Is triggered after a double (two finger) tap gesture
 *	obj.description:
 *		taptwo
 * *
 * @event 'tapthree'
 *		Is triggered after a tripple (three finger) tap gesture
 *	obj.description:
 *		tapthree
 *
 *
 * Gestureend events:
 *	A gesture is an interpretation of different touchpoints.
 *	The jGesture-Events in this group are triggered when a gesture has finished
 *	and the touchpoints are removed from the device.
 *
 * @event 'pinchopen'
 *		Is triggered when a pinchopen gesture (two fingers moving away from each
 *		other) occured and the touchpoints (fingers) are removed the device.
 *	obj.description:
 *		pinch:+1:open
 *
 * @event 'pinchclose'
 *		Is triggered when a pinchclose gesture (two fingers moving towards each
 *		other) occured and the touchpoints (fingers) are removed the device.
 *	obj.description:
 *		pinch:-1:close
 *
 * @event 'rotatecw'
 *		Is triggered when a clockwise rotation gesture (two fingers rotating
 *		clockwise) occured and the touchpoints (fingers) are removed the device.
 *	obj.description:
 *		rotate:+1:+clockwise
 *
 * @event 'rotateccw'
 *		Is triggered when a counterclockwise rotation gesture (two fingers
 *		rotating counterclockwise) occured and the touchpoints (fingers) are
 *		removed the device.
 *	obj.description:
 *		rotate:-1:+counterclockwise
 *
 *
 * Motion events:
 *  A "motion event" is an interpretation of changes in space, e.g. a "shaking motion"
 *  consists of a specified number of acceleration changes in a given interval.
 * For understanding "directions", place your mobile device on a table with the bottom
 * (home button) close to you:
 *  - x-axis: horizontal left / right
 *  - y-axis: horizontal front / back (through the home button)
 *  - z-axis: vertical through your device
 *
 *  Note: Devicemotion / deviceorientation don't send custom event (such as: jGestures.touchstart).
 *  Note: Devicemotion should be bound on the "window-element" - because the whole device moves
 *
 * @event 'shake'
 *		Is triggered when a shaking motion is detected
 *	obj.description:
 *		shake:leftright:x-axisfrontback:y-axis:updown:z-axis
 *
 * @event 'shakefrontback'
 *		Is triggered when a shaking motion is detected and the gesture can be interpreted as a mainly front-back movement.
  *	obj.description:
 *		shakefrontback:shakefrontback:y-axis
 *
 * @event 'shakeleftright'
 *		Is triggered when a shaking motion is detected and the gesture can be interpreted as a mainly left-right movement.
 *		Additional major movements are mentioned in the obj.description.
 *	obj.description:
 *		shakeleftright:leftright:x-axis
 *
 * @event 'shakeupdown'
 *		Is triggered when a shaking motion is detected and the gesture can be interpreted as a mainly up-down movement.
 *		Additional major movements are mentioned in the obj.description.
 *	obj.description:
 *		shake:shakeupdown:updown:z-axis
 *
 * @example
 *		.bind( eventType, [ eventData ], handler(eventObject) )
 * jQuery('body').bind('tapone',function(){alert(arguments[1].description);})
 *
 */

 (function($) {

	/**
	* General thresholds.
	*/
	// @TODO: move to $...defaults
	// @TODO: shake to defaults freeze etc
	// change of x deg in y ms


	$.jGestures = {};
	$.jGestures.defaults = {};
	$.jGestures.defaults.thresholdShake =  {
		requiredShakes : 10,
		freezeShakes: 100,
		frontback : {
			sensitivity: 10
		 },
		leftright : {
			sensitivity: 10
		},
		updown : {
			sensitivity: 10
		}
	};

	$.jGestures.defaults.thresholdPinchopen = 0.05;
	$.jGestures.defaults.thresholdPinchmove = 0.05;
	$.jGestures.defaults.thresholdPinch = 0.05;
	$.jGestures.defaults.thresholdPinchclose = 0.05;
	$.jGestures.defaults.thresholdRotatecw = 5; //deg
	$.jGestures.defaults.thresholdRotateccw = 5; // deg
	// a tap becomes a swipe if x/y values changes are above this threshold
	$.jGestures.defaults.thresholdMove = 20;
	$.jGestures.defaults.thresholdSwipe = 100;
	// get capable user agents
	$.jGestures.data = {};
	$.jGestures.data.capableDevicesInUserAgentString = ['iPad','iPhone','iPod','Mobile Safari']; // basic functionality such as swipe, pinch, rotate, tap should work on every mobile safari, e.g. GalaxyTab
	$.jGestures.data.hasGestures = (function () { var _i; for(_i = 0; _i < $.jGestures.data.capableDevicesInUserAgentString.length; _i++ ) {  if (navigator.userAgent.indexOf($.jGestures.data.capableDevicesInUserAgentString[_i]) !== -1 ) {return true;} } return false; } )();
	$.hasGestures = $.jGestures.data.hasGestures;
	$.jGestures.events = {
		touchstart : 'jGestures.touchstart',
		touchendStart: 'jGestures.touchend;start',
		touchendProcessed: 'jGestures.touchend;processed',
		gesturestart: 'jGestures.gesturestart',
		gestureendStart: 'jGestures.gestureend;start',
		gestureendProcessed: 'jGestures.gestureend;processed'
	};

	jQuery
		.each({
			// "first domevent necessary"_"touch event+counter" : "exposed as"
			// event: orientationchange
			orientationchange_orientationchange01: "orientationchange",
			// event: gestures
			gestureend_pinchopen01: "pinchopen",
			gestureend_pinchclose01: "pinchclose",
			gestureend_rotatecw01 : 'rotatecw',
			gestureend_rotateccw01 : 'rotateccw',
			// move events
			gesturechange_pinch01: 'pinch',
			gesturechange_rotate01: 'rotate',
			touchstart_swipe13: 'swipemove',
			// event: touches
			touchstart_swipe01: "swipeone",
			touchstart_swipe02: "swipetwo",
			touchstart_swipe03: "swipethree",
			touchstart_swipe04: "swipefour",
			touchstart_swipe05: 'swipeup',
			touchstart_swipe06: 'swiperightup',
			touchstart_swipe07: 'swiperight',
			touchstart_swipe08: 'swiperightdown',
			touchstart_swipe09: 'swipedown',
			touchstart_swipe10: 'swipeleftdown',
			touchstart_swipe11: 'swipeleft',
			touchstart_swipe12: 'swipeleftup',
			touchstart_tap01: 'tapone',
			touchstart_tap02: 'taptwo',
			touchstart_tap03: 'tapthree',
			touchstart_tap04: 'tapfour',

			devicemotion_shake01: 'shake',
			devicemotion_shake02: 'shakefrontback',
			devicemotion_shake03: 'shakeleftright',
			devicemotion_shake04: 'shakeupdown'

		},

		/**
		* Add gesture events inside the jQuery.event.special namespace
		*/
		function( sInternal_, sPublicFN_ ) {

			// add as funciton to jQuery.event.special.sPublicFN_
			jQuery.event.special[ sPublicFN_ ] = {

				/**
				* When the first event handler is bound, jQuery executes the setup function.
				* This plugin just uses one eventhandler per element, regardless of the number of bound events.
				* All Events are stored internally as properties on the dom-element using the $.data api.
				* The setup-function adds the eventlistener, acting as a proxy function for the internal events.
				* $.data.ojQueryGestures[_sDOMEvent ('tap') ] = {Boolean}
				* @return {Void}
				*/
				setup: function () {
					// split the arguments to necessary controll arguements
					var _aSplit = sInternal_.split('_');
					var _sDOMEvent = _aSplit[0]; //
					// get the associated gesture event and strip the counter: necessary for distinguisihng similliar events such as tapone-tapfour
					var _sGestureEvent = _aSplit[1].slice(0,_aSplit[1].length-2);
					var _$element = jQuery(this);
					var _oDatajQueryGestures ;
					var oObj;
					// bind the event handler on the first $.bind() for a gestureend-event, set marker
					if (!_$element.data('ojQueryGestures') || !_$element.data('ojQueryGestures')[_sDOMEvent])  {
						// setup pseudo event
						_oDatajQueryGestures = _$element.data('ojQueryGestures') || {};
						oObj = {};
						// marker for:  domEvent being set on this element
						// e.g.: $.data.oGestureInternals['touchstart'] = true;
						// since they're grouped, i'm just marking the first one being added
						oObj[_sDOMEvent] = true;
						$.extend(true,_oDatajQueryGestures,oObj);
						_$element.data('ojQueryGestures' ,_oDatajQueryGestures);
						// add gesture events
						if($.hasGestures) {
							switch(_sGestureEvent) {

								// event: orientationchange
								case 'orientationchange':
									_$element.get(0).addEventListener('orientationchange', _onOrientationchange, false);
								break;

								// event:
								// - shake
								// - tilt
								case 'shake':
								case 'shakefrontback':
								case 'shakeleftright':
								case 'shakeupdown':
								case 'tilt':
									//$.hasGyroscope = true //!window.DeviceOrientationEvent;
									//_$element.get(0).addEventListener('devicemotion', _onDevicemotion, false);
									//_$element.get(0).addEventListener('deviceorientation', _onDeviceorientation, false);
									_$element.get(0).addEventListener('devicemotion', _onDevicemotion, false);
								break;

								// event:
								// - touchstart
								// - touchmove
								// - touchend
								case 'tap':
								case 'swipe':
								case 'swipeup':
								case 'swiperightup':
								case 'swiperight':
								case 'swiperightdown':
								case 'swipedown':
								case 'swipeleftdown':
								case 'swipeleft':
									_$element.get(0).addEventListener('touchstart', _onTouchstart, false);
								break;

								// event: gestureend
								case 'pinchopen':
								case 'pinchclose' :
								case 'rotatecw' :
								case 'rotateccw' :
									_$element.get(0).addEventListener('gesturestart', _onGesturestart, false);
									_$element.get(0).addEventListener('gestureend', _onGestureend, false);
								break;

								// event: gesturechange
								case 'pinch':
								case 'rotate':
									_$element.get(0).addEventListener('gesturestart', _onGesturestart, false);
									_$element.get(0).addEventListener('gesturechange', _onGesturechange, false);
								break;
							}
						}
						// create substitute for gesture events
						else {
							switch(_sGestureEvent) {
								// event substitutes:
								// - touchstart: mousedown
								// - touchmove: none
								// - touchend: mouseup
								case 'tap':
								case 'swipe':
									// _$element.get(0).addEventListener('mousedown', _onTouchstart, false);
									 _$element.bind('mousedown', _onTouchstart);
								break;

								// no substitution
								case 'orientationchange':
								case 'pinchopen':
								case 'pinchclose' :
								case 'rotatecw' :
								case 'rotateccw' :
								case 'pinch':
								case 'rotate':
								case 'shake':
								case 'tilt':

								break;
							}
						}

					}
					return false;
				},

				/**
				* For every $.bind(GESTURE) the add-function will be called.
				* Instead of binding an actual eventlister, the event is stored as $.data on the element.
				* The handler will be triggered using $.triggerHandler(GESTURE) if the internal
				* eventhandler (proxy being bound on setup()) detects a GESTURE event
				* @param {Object} event_ jQuery-Event-Object being passed by $.bind()
				* @return {Void}
				*/
				add : function(event_) {
					// add pseudo event: properties on $.data
					var _$element = jQuery(this);
					var _oDatajQueryGestures = _$element.data('ojQueryGestures');
//					_oDatajQueryGestures[event_.type] = { 'originalType' : event_.type , 'threshold' : event_.data.threshold, 'preventDefault' : event_.data.preventDefault } ;
					_oDatajQueryGestures[event_.type] = { 'originalType' : event_.type } ;
					return false;
				},

				/**
				* For every $.unbind(GESTURE) the remove-function will be called.
				* Instead of removing the actual eventlister, the event is removed from $.data on the element.
				* @param {Object} event_ jQuery-Event-Object being passed by $.bind()
				* @return {Void}
				*/
				remove : function(event_) {
					// remove pseudo event: properties on $.data
					var _$element = jQuery(this);
					var _oDatajQueryGestures = _$element.data('ojQueryGestures');
					_oDatajQueryGestures[event_.type] = false;
					_$element.data('ojQueryGestures' ,_oDatajQueryGestures );
					return false;
				},

				/**
				* The last $.unbind()-call on the domElement triggers the teardown function
				* removing the eventlistener
				* @return {Void}
				*/
				// @TODO: maybe rework teardown to work with event type?!
				teardown : function() {
					// split the arguments to necessary controll arguements
					var _aSplit = sInternal_.split('_');
					var _sDOMEvent = _aSplit[0]; //
					// get the associated gesture event and strip the counter: necessary for distinguisihng similliar events such as tapone-tapfour
					var _sGestureEvent = _aSplit[1].slice(0,_aSplit[1].length-2);
					var _$element = jQuery(this);
					var _oDatajQueryGestures;
					var oObj;
					// bind the event handler on the first $.bind() for a gestureend-event, set marker
					if (!_$element.data('ojQueryGestures') || !_$element.data('ojQueryGestures')[_sDOMEvent])  {
						// setup pseudo event
						_oDatajQueryGestures = _$element.data('ojQueryGestures') || {};
						oObj = {};
						// remove marker for:  domEvent being set on this element
						oObj[_sDOMEvent] = false;
						$.extend(true,_oDatajQueryGestures,oObj);
						_$element.data('ojQueryGestures' ,_oDatajQueryGestures);

						// remove gesture events
						if($.hasGestures) {
							switch(_sGestureEvent) {

								// event: orientationchange
								case 'orientationchange':
									_$element.get(0).removeEventListener('orientationchange', _onOrientationchange, false);
								break;

								case 'shake':
								case 'shakefrontback':
								case 'shakeleftright':
								case 'shakeupdown':
								case 'tilt':
									_$element.get(0).removeEventListener('devicemotion', _onDevicemotion, false);
								break;

								// event :
								// - touchstart
								// - touchmove
								// - touchend
								case 'tap':
								case 'swipe':
								case 'swipeup':
								case 'swiperightup':
								case 'swiperight':
								case 'swiperightdown':
								case 'swipedown':
								case 'swipeleftdown':
								case 'swipeleft':
								case 'swipeleftup':
									_$element.get(0).removeEventListener('touchstart', _onTouchstart, false);
									_$element.get(0).removeEventListener('touchmove', _onTouchmove, false);
									_$element.get(0).removeEventListener('touchend', _onTouchend, false);
								break;

								// event: gestureend
								case 'pinchopen':
								case 'pinchclose' :
								case 'rotatecw' :
								case 'rotateccw' :
									_$element.get(0).removeEventListener('gesturestart', _onGesturestart, false);
									_$element.get(0).removeEventListener('gestureend', _onGestureend, false);
								break;

								// event: gesturechange
								case 'pinch':
								case 'rotate':
									_$element.get(0).removeEventListener('gesturestart', _onGesturestart, false);
									_$element.get(0).removeEventListener('gesturechange', _onGesturechange, false);
								break;
							}
						}
						// remove substitute for gesture events
						else {
							switch(_sGestureEvent) {
								// event substitutes:
								// - touchstart: mousedown
								// - touchmove: none
								// - touchend: mouseup
								case 'tap':
								case 'swipe':
//									_$element.get(0).removeEventListener('mousedown', _onTouchstart, false);
//									_$element.get(0).removeEventListener('mousemove', _onTouchmove, false);
//									_$element.get(0).removeEventListener('mouseup', _onTouchend, false);
									_$element.unbind('mousedown', _onTouchstart);
									_$element.unbind('mousemove', _onTouchmove);
									_$element.unbind('mouseup', _onTouchend);
								break;

								// no substitution
								case 'orientationchange':
								case 'pinchopen':
								case 'pinchclose' :
								case 'rotatecw' :
								case 'rotateccw' :
								case 'pinch':
								case 'rotate':
								case 'shake':
								case 'tilt':

								break;
							}
						}

					}
				return false;
				}

			};
		});

	/**
	* Creates the object that ist passed as second argument to the $element.triggerHandler function.
	* This object contains detailed informations about the gesture event.
	* @param {Object} oOptions_  {type: {String}, touches: {String}, deltaY: {String},deltaX : {String}, startMove: {Object}, event:{DOM-Event}, timestamp:{String},vector: {Number}}
	* @example _createOptions (
	*				{
	*					type: 'swipemove',
	*					touches: '1',
	*					deltaY: _iDeltaY,
	*					deltaX : _iDeltaX,
	*					startMove: _oDatajQueryGestures.oStartTouch,
	*					event:event_,
	*					timestamp:_oEventData.timestamp,
	*					vector: -1
	*				}
	*			);
	* @returns {Object}
	*			{
	*				type: eventtype e.g. "swipe","pinch",
	*				originalEvent: {DOM-Event},
	*				// default: just one entry on the delta-array - the first touchpoint
	*				// the first touchpoint is the reference point for every gesture,
	*				// because moving touchpoints in various directions would result in
	*				// a gesture.
	*				// delta and direction details are just provided for touch not for gesture / motion events
	*				delta : [
	*					{
	*						lastX:{Number} , // x-axis: relative to the last touchevent (e.g. touchmove!)
	*						lastY:{Number}, // y-axis: relative to the last touchevent (e.g. touchmove!)
	*						moved: {Number},  // distance: relative to the original touchpoint
	*						startX: {Number} , // relative to the original touchpoint
	*						startY: {Number} ,// relative to the original touchpoint
	*					} ],
	*				// based on the first touchpoint
	*				direction : { // relative to the last touchevent (e.g. touchmove!)
	*					vector: {Number}, // -1|+1, indicates the direction if necessary(pinch/rotate)
	*					orientation: {Number} // window.orientation: -90,0,90,180 || null (window.orienntation)
	*					lastX : {Number}, // -1,0,+1 relative to the last touchevent (e.g. touchmove!)
	*					lastY : {Number}, // -1,0,+1 relative to the last touchevent (e.g. touchmove!)
	*					startX: {Number} , //-1,0,+1 relative to the original touchpoint
	*					startY: {Number} ,// -1,0,+1 relative to the original touchpoint
	*				},
	*				rotation: {Number} || null, // gestureonly: amount of rotation relative to the current position NOT the original
	*				scale: {Number} || null, // gestureonly: amount of scaling relative to the current position NOT the original
	*				duration: {Number}, // ms: relative to the original touchpoint
	*				description : {String} // details as String: {TYPE *}:{TOUCHES 1|2|3|4}:{X-AXIS 'right'|'left'|'steady'}:{Y-AXIS 'down'|'up'|'steady'} e.g. "swipe:1:left:steady" relative to the last touchpoint
	*			};
	*/
	function _createOptions(oOptions_) {
		// force properties
		oOptions_.startMove = (oOptions_.startMove) ? oOptions_.startMove : {startX: null,startY:null,timestamp:null}  ;
		var _iNow = new Date().getTime();
		var _oDirection;
		var _oDelta;
		// calculate touch differences
		if (oOptions_.touches) {
			// store delta values
			_oDelta = [
				{
					lastX: oOptions_.deltaX ,
					lastY: oOptions_.deltaY,
					moved: null,
					startX:  oOptions_.screenX - oOptions_.startMove.screenX ,
					startY: oOptions_.screenY - oOptions_.startMove.screenY
				}
			];

			_oDirection =  {
				vector: oOptions_.vector || null,
				orientation : window.orientation || null,
				lastX : ((_oDelta[0].lastX > 0) ? +1 : ( (_oDelta[0].lastX < 0) ? -1 : 0 ) ),
				lastY : ((_oDelta[0].lastY > 0) ? +1 : ( (_oDelta[0].lastY < 0) ? -1 : 0 ) ),
				startX : ((_oDelta[0].startX > 0) ? +1 : ( (_oDelta[0].startX < 0) ? -1 : 0 ) ),
				startY : ((_oDelta[0].startY > 0) ? +1 : ( (_oDelta[0].startY < 0) ? -1 : 0 ) )
			};

			// calculate distance traveled using the pythagorean theorem
			_oDelta[0].moved =  Math.sqrt(Math.pow(Math.abs(_oDelta[0].startX), 2) + Math.pow(Math.abs(_oDelta[0].startY), 2));

		}
		return {
			type: oOptions_.type || null,
			originalEvent: oOptions_.event || null,
			delta : _oDelta  || null,
			direction : _oDirection || { orientation : window.orientation || null, vector: oOptions_.vector || null},
			duration: (oOptions_.duration) ? oOptions_.duration : ( oOptions_.startMove.timestamp ) ? _iNow - oOptions_.timestamp : null,
			rotation: oOptions_.rotation || null,
			scale: oOptions_.scale || null,
			description : oOptions_.description || [
				oOptions_.type,
				':',
				oOptions_.touches,
				':',
				((_oDelta[0].lastX != 0) ? ((_oDelta[0].lastX > 0) ? 'right' : 'left') : 'steady'),
				':',
				((_oDelta[0].lastY != 0) ? ( (_oDelta[0].lastY > 0) ? 'down' : 'up') :'steady')
				].join('')
		};

	}



	/**
	* DOM-event handlers
	*/

	/**
	* Handler: orientationchange
	* Triggers the bound orientationchange handler on the window element
	* The "orientationchange" handler will receive an object with additional information
	* about the event.
	*  {
	*	direction : {
	*		orientation: {-90|0|90|180}
	*	},
	*	description : [
	*		'orientationchange:{landscape:clockwise:|portrait:default|landscape:counterclockwise|portrait:upsidedown}:{-90|0|90|180}' // e.g. 'orientation:landscape:clockwise:-90
	*	}
	* @param {DOM-Event} event_
	* @return {Void}
	*/
	function _onOrientationchange(event_) {

		// window.orientation: -90,0,90,180
		var _aDict = ['landscape:clockwise:','portrait:default:','landscape:counterclockwise:','portrait:upsidedown:'];

		$(window).triggerHandler('orientationchange',
			{
				direction : {orientation: window.orientation},
				description : [
					'orientationchange:',
					_aDict[( (window.orientation / 90) +1)],
					window.orientation
					].join('')
			});
	}


	/**
	* Handler: devicemotion
	* Calculates "motion events" such as shake, tilt, wiggle by observing "changes in space"
	* For understanding "directions", place your mobile device on a table with the bottom
	* (home button) close to you:
	*  - x-axis: horizontal left / right
	*  - y-axis: horizontal front / back (through the home button)
	*  - z-axis: vertical through your device
	* @param {DOM-Event} event_
	* @returns {Object}
	*			{
	*				type: eventtype e.g. "shake",
	*				originalEvent: {DOM-Event},
	*				// delta and direction details are just provided for touch not for gesture / motion events
	*				delta : null,
	*				direction :{
	*					vector: null,
	*					orientation: -90,0,90,180 || null (window.orienntation)
	*				}
	*				rotation: {Number} , //  amount of rotation relative to the current position NOT the original
	*				scale: {Number} , // amount of scaling relative to the current position NOT the original
	*				duration: {Number}, // ms: duration of the motion
	*				description : {String} // details as String: pinch:{'close'|'open'} e.g. "pinch:-1:close" ||  rotate:{'counterclockwise'|'clockwise'} e.g. "rotate:-1:counterclockwise"
	*			};
	* @param {DOM-Event} event_
	* @return {Void}
	*/
	function _onDevicemotion(event_) {

		var _sType;
		var _$element = jQuery(window);
		//var _bHasGyroscope = $.hasGyroscope;

		// skip custom notification: devicemotion is triggered every 0.05s regardlesse of any gesture

		// get options
		var _oDatajQueryGestures = _$element.data('ojQueryGestures');

		var _oThreshold = $.jGestures.defaults.thresholdShake;

		// get last position or set initital values
		var _oLastDevicePosition = _oDatajQueryGestures.oDeviceMotionLastDevicePosition || {
			accelerationIncludingGravity : {
				x: 0,
				y: 0,
				z: 0
			},
			shake : {
				eventCount: 0,
				intervalsPassed: 0,
				intervalsFreeze: 0
			},
			shakeleftright : {
				eventCount: 0,
				intervalsPassed: 0,
				intervalsFreeze: 0
			},
			shakefrontback : {
				eventCount: 0,
				intervalsPassed: 0,
				intervalsFreeze: 0
			},
			shakeupdown : {
				eventCount: 0,
				intervalsPassed: 0,
				intervalsFreeze: 0
			}
		};

		// cache current values
		var _oCurrentDevicePosition = {
			accelerationIncludingGravity : {
				x: event_.accelerationIncludingGravity.x,
				y: event_.accelerationIncludingGravity.y,
				z: event_.accelerationIncludingGravity.z
			},
			shake: {
				eventCount: _oLastDevicePosition.shake.eventCount,
				intervalsPassed: _oLastDevicePosition.shake.intervalsPassed,
				intervalsFreeze: _oLastDevicePosition.shake.intervalsFreeze
			 },
			 shakeleftright: {
				eventCount: _oLastDevicePosition.shakeleftright.eventCount,
				intervalsPassed: _oLastDevicePosition.shakeleftright.intervalsPassed,
				intervalsFreeze: _oLastDevicePosition.shakeleftright.intervalsFreeze
			 },
			 shakefrontback: {
				eventCount: _oLastDevicePosition.shakefrontback.eventCount,
				intervalsPassed: _oLastDevicePosition.shakefrontback.intervalsPassed,
				intervalsFreeze: _oLastDevicePosition.shakefrontback.intervalsFreeze
			 },
			 shakeupdown: {
				eventCount: _oLastDevicePosition.shakeupdown.eventCount,
				intervalsPassed: _oLastDevicePosition.shakeupdown.intervalsPassed,
				intervalsFreeze: _oLastDevicePosition.shakeupdown.intervalsFreeze
			 }

		};


		// options
		var _aType;
		var _aDescription;
		var _oObj;


		// trigger events for all bound pseudo events on this element
		for (_sType in _oDatajQueryGestures) {
			// get current pseudo event


			// trigger bound events on this element
			switch(_sType) {

				case 'shake':
				case 'shakeleftright':
				case 'shakefrontback':
				case 'shakeupdown':

					// options
					_aType = [];
					_aDescription = [];

					_aType.push(_sType);

					// freeze shake - prevent multiple shake events on one  shaking motion (user won't stop shaking immediately)
					if (++_oCurrentDevicePosition[_sType].intervalsFreeze > _oThreshold.freezeShakes && _oCurrentDevicePosition[_sType].intervalsFreeze < (2*_oThreshold.freezeShakes) ) { break;	}

					// set control values
					_oCurrentDevicePosition[_sType].intervalsFreeze  = 0;
					_oCurrentDevicePosition[_sType].intervalsPassed++;

					// check for shaking motions: massive acceleration changes in every direction
					if ( ( _sType === 'shake' ||_sType === 'shakeleftright' ) && ( _oCurrentDevicePosition.accelerationIncludingGravity.x > _oThreshold.leftright.sensitivity  || _oCurrentDevicePosition.accelerationIncludingGravity.x < (-1* _oThreshold.leftright.sensitivity) ) ) {
						_aType.push('leftright');
						_aType.push('x-axis');
					}

					if ( ( _sType === 'shake' ||_sType === 'shakefrontback' ) && (_oCurrentDevicePosition.accelerationIncludingGravity.y > _oThreshold.frontback.sensitivity  || _oCurrentDevicePosition.accelerationIncludingGravity.y < (-1 * _oThreshold.frontback.sensitivity) ) ) {
						_aType.push('frontback');
						_aType.push('y-axis');
					}

					if ( ( _sType === 'shake' ||_sType === 'shakeupdown' ) && ( _oCurrentDevicePosition.accelerationIncludingGravity.z+9.81 > _oThreshold.updown.sensitivity  || _oCurrentDevicePosition.accelerationIncludingGravity.z+9.81 < (-1 * _oThreshold.updown.sensitivity) ) ) {
						_aType.push('updown');
						_aType.push('z-axis');
					}

					// at least one successful shaking event
					if (_aType.length > 1) {
						// minimum number of shaking motions during  the defined "time" (messured by events - device event interval: 0.05s)
						if (++_oCurrentDevicePosition[_sType].eventCount == _oThreshold.requiredShakes && (_oCurrentDevicePosition[_sType].intervalsPassed) < _oThreshold.freezeShakes ) {
							// send event
							_$element.triggerHandler(_sType, _createOptions ({type: _sType, description: _aType.join(':'), event:event_,duration:_oCurrentDevicePosition[_sType].intervalsPassed*5 }) );
							// reset
							_oCurrentDevicePosition[_sType].eventCount = 0;
							_oCurrentDevicePosition[_sType].intervalsPassed = 0;
							// freeze shake
							_oCurrentDevicePosition[_sType].intervalsFreeze = _oThreshold.freezeShakes+1;
						}
						// too slow, reset
						else if (_oCurrentDevicePosition[_sType].eventCount == _oThreshold.requiredShakes && (_oCurrentDevicePosition[_sType].intervalsPassed) > _oThreshold.freezeShakes ) {
							_oCurrentDevicePosition[_sType].eventCount = 0 ;
							_oCurrentDevicePosition[_sType].intervalsPassed = 0;
						}
					}
				break;

			}

			// refresh pseudo events
			_oObj = {};
			_oObj.oDeviceMotionLastDevicePosition = _oCurrentDevicePosition;
			_$element.data('ojQueryGestures',$.extend(true,_oDatajQueryGestures,_oObj));

		}
	}


	/**
	* Handler: touchstart or mousedown
	* Setup pseudo-event by storing initial values such as :
	*	screenX : {Number}
	*	screenY : {Number}
	*	timestamp: {Number}
	*  on the pseudo gesture event and
	*  sets up additional eventlisteners for handling touchmove events.
	* @param {DOM-Event} event_
	* @return {Void}
	*/
	function _onTouchstart(event_) {

		// ignore bubbled handlers
		// if ( event_.currentTarget !== event_.target ) { return; }

		var _$element = jQuery(event_.currentTarget);
		// var _$element = jQuery(event_.target);

		// trigger custom notification
		_$element.triggerHandler($.jGestures.events.touchstart,event_);


		// set the necessary touch events
		if($.hasGestures) {
			event_.currentTarget.addEventListener('touchmove', _onTouchmove, false);
			event_.currentTarget.addEventListener('touchend', _onTouchend, false);
		}
		// event substitution
		else {
//			event_.currentTarget.addEventListener('mousemove', _onTouchmove, false);
//			event_.currentTarget.addEventListener('mouseup', _onTouchend, false);
			_$element.bind('mousemove', _onTouchmove);
			_$element.bind('mouseup', _onTouchend);
		}

		// get stored pseudo event
		var _oDatajQueryGestures = _$element.data('ojQueryGestures');

		// var _oEventData = _oDatajQueryGestures[_sType];
		var _eventBase = (event_.touches) ? event_.touches[0] : event_;
		// store current values for calculating relative values (changes between touchmoveevents)
		var _oObj = {};
		_oObj.oLastSwipemove = { screenX : _eventBase.screenX, screenY : _eventBase.screenY, timestamp:new Date().getTime()};
		_oObj.oStartTouch = { screenX : _eventBase.screenX, screenY : _eventBase.screenY, timestamp:new Date().getTime()};

		_$element.data('ojQueryGestures',$.extend(true,_oDatajQueryGestures,_oObj));
	}


	/**
	* Handler: touchmove or mousemove
	* Calculates the x/y changes since the last event,
	* compares it to $.jGestures.defaults.thresholdMove and triggers
	* an swipemove event if the distance exceed the
	* threshold.
	* Custom-event argument object:
	* {Object}
	*			{
	*				type: e.g. 'swipemove',
	*				¡Ö: {DOM-Event},
	*				// default: just one entry on the delta-array - the first touchpoint
	*				// the first touchpoint is the reference point for every gesture,
	*				// because moving touchpoints in various directions would result in
	*				// a gesture.
	*				// delta and direction details are just provided for touch not for gesture / motion events
	*				delta : [
	*					{
	*						lastX:{Number} , // x-axis: relative to the last touchevent (e.g. touchmove!)
	*						lastY:{Number}, // y-axis: relative to the last touchevent (e.g. touchmove!)
	*						moved: {Number},  // distance: relative to the original touchpoint
	*						startX: {Number} , // relative to the original touchpoint
	*						startY: {Number} ,// relative to the original touchpoint
	*					} ],
	*				// based on the first touchpoint
	*				direction : { // relative to the last touchevent (e.g. touchmove!)
	*					vector: {Number}, // -1|+1, indicates the direction if necessary(pinch/rotate)
	*					orientation: {Number} // window.orientation: -90,0,90,180 || null (window.orienntation)
	*					lastX : {Number}, // -1,0,+1 relative to the last touchevent (e.g. touchmove!)
	*					lastY : {Number}, // -1,0,+1 relative to the last touchevent (e.g. touchmove!)
	*					startX: {Number} , //-1,0,+1 relative to the original touchpoint
	*					startY: {Number} ,// -1,0,+1 relative to the original touchpoint
	*				},
	*				rotation: null, // gestureonly: amount of rotation relative to the current position NOT the original
	*				scale: null, // gestureonly: amount of scaling relative to the current position NOT the original
	*				duration: {Number}, // ms: relative to the original touchpoint
	*				description : {String} // details as String: {TYPE *}:{TOUCHES 1|2|3|4}:{X-AXIS 'right'|'left'|'steady'}:{Y-AXIS 'down'|'up'|'steady'} e.g. "swipe:1:left:steady" relative to the last touchpoint
	*			};
	*
	* @param {DOM-Event} event_
	* @return {Void}
	*/
	function _onTouchmove(event_) {

		var _$element = jQuery(event_.currentTarget);
		// var _$element = jQuery(event_.target);

		// get stored pseudo event
		var _oDatajQueryGestures = _$element.data('ojQueryGestures');

		var _bHasTouches = !!event_.touches;
		var _iScreenX = (_bHasTouches) ? event_.changedTouches[0].screenX : event_.screenX;
		var _iScreenY = (_bHasTouches) ? event_.changedTouches[0].screenY : event_.screenY;

		//relative to the last event
		var _oEventData = _oDatajQueryGestures.oLastSwipemove;
		var _iDeltaX = _iScreenX - _oEventData.screenX   ;
		var _iDeltaY = _iScreenY - _oEventData.screenY;

		var _oDetails;

			// there's a swipemove set (not the first occurance), trigger event
		if (!!_oDatajQueryGestures.oLastSwipemove) {
			// check
			_oDetails = _createOptions({type: 'swipemove', touches: (_bHasTouches) ? event_.touches.length: '1', screenY: _iScreenY,screenX:_iScreenX ,deltaY: _iDeltaY,deltaX : _iDeltaX, startMove:_oEventData, event:event_, timestamp:_oEventData.timestamp});
			_$element.triggerHandler(_oDetails.type,_oDetails);
		}
		// store the new values
		var _oObj = {};
		var _eventBase = (event_.touches) ? event_.touches[0] : event_;
		_oObj.oLastSwipemove = { screenX : _eventBase.screenX, screenY : _eventBase.screenY, timestamp:new Date().getTime()};
		_$element.data('ojQueryGestures',$.extend(true,_oDatajQueryGestures,_oObj));
	}


	/**
	* Handler: touchend or mouseup
	* Removes the additional handlers (move/end)
	* Calculates the x/y changes since the touchstart event
	* not in relation to the last move event.
	* Triggers the
	*	swipeone|swipetwo|swipethree|swipefour|
	*	swipeup|swiperightup|swiperight|swiperightdown|swipedown|
	*	swipeleftdown|swipeleft|swipeleftup|
	*	tapone|taptwo|tapthree|tapfour
	* event.
	*		{Object}
	*			{
	*				type: eventtype e.g. "swipeone","swipeleftdown",
	*				originalEvent: {DOM-Event},
	*				// default: just one entry on the delta-array - the first touchpoint
	*				// the first touchpoint is the reference point for every gesture,
	*				// because moving touchpoints in various directions would result in
	*				// a gesture.
	*				// delta and direction details are just provided for touch not for gesture / motion events
	*				delta : [
	*					{
	*						lastX:{Number} , // x-axis: relative to the last touchevent (e.g. touchmove!)
	*						lastY:{Number}, // y-axis: relative to the last touchevent (e.g. touchmove!)
	*						moved: {Number},  // distance: relative to the original touchpoint
	*						startX: {Number} , // relative to the original touchpoint
	*						startY: {Number} ,// relative to the original touchpoint
	*					} ],
	*				// based on the first touchpoint
	*				direction : { // relative to the last touchevent (e.g. touchmove!)
	*					vector: {Number}, // -1|+1, indicates the direction if necessary(pinch/rotate)
	*					orientation: {Number} // window.orientation: -90,0,90,180 || null (window.orienntation)
	*					lastX : {Number}, // -1,0,+1 relative to the last touchevent (e.g. touchmove!)
	*					lastY : {Number}, // -1,0,+1 relative to the last touchevent (e.g. touchmove!)
	*					startX: {Number} , //-1,0,+1 relative to the original touchpoint
	*					startY: {Number} ,// -1,0,+1 relative to the original touchpoint
	*				},
	*				rotation: null,
	*				scale: null ,
	*				duration: {Number}, // ms: relative to the original touchpoint
	*				description : {String} // details as String: {TYPE *}:{TOUCHES 1|2|3|4}:{X-AXIS 'right'|'left'|'steady'}:{Y-AXIS 'down'|'up'|'steady'} e.g. "swipe:1:left:steady" relative to the last touchpoint
	*			};
	* @param {DOM-Event} event_
	* @return {Void}
	*/
	function _onTouchend(event_) {

		// ignore bubbled handlers
		// if ( event_.currentTarget !== event_.target ) { return; }

		var _$element = jQuery(event_.currentTarget);
		var _bHasTouches = !!event_.changedTouches;
		var _iTouches = (_bHasTouches) ? event_.changedTouches.length : '1';
		var _iScreenX = (_bHasTouches) ? event_.changedTouches[0].screenX : event_.screenX;
		var _iScreenY = (_bHasTouches) ? event_.changedTouches[0].screenY : event_.screenY;

		// trigger custom notification
		_$element.triggerHandler($.jGestures.events.touchendStart,event_);

		// var _$element = jQuery(event_.target);
		// remove events
		if($.hasGestures) {
			event_.currentTarget.removeEventListener('touchmove', _onTouchmove, false);
			event_.currentTarget.removeEventListener('touchend', _onTouchend, false);
		}
		// event substitution
		else {
//			event_.currentTarget.removeEventListener('mousemove', _onTouchmove, false);
//			event_.currentTarget.removeEventListener('mouseup', _onTouchend, false);
			_$element.unbind('mousemove', _onTouchmove);
			_$element.unbind('mouseup', _onTouchend);
		}
		// get all bound pseudo events
		var _oDatajQueryGestures = _$element.data('ojQueryGestures');

		// if the current change on the x/y position is above the defined threshold for moving an element set the moved flag
		// to distinguish between a moving gesture and a shaking finger trying to tap
		var _bHasMoved = (
			Math.abs(_oDatajQueryGestures.oStartTouch.screenX - _iScreenX) > $.jGestures.defaults.thresholdMove ||
			Math.abs(_oDatajQueryGestures.oStartTouch.screenY - _iScreenY) > $.jGestures.defaults.thresholdMove
		) ? true : false;

		// if the current change on the x/y position is above the defined threshold for swiping set the moved flag
		// to indicate we're dealing with a swipe gesture
		var _bHasSwipeGesture = (
			Math.abs(_oDatajQueryGestures.oStartTouch.screenX - _iScreenX) > $.jGestures.defaults.thresholdSwipe ||
			Math.abs(_oDatajQueryGestures.oStartTouch.screenY - _iScreenY) > $.jGestures.defaults.thresholdSwipe
		) ? true : false;


		var _sType;
		var _oEventData ;

		var _oDelta;

		// calculate distances in relation to the touchstart position not the last touchmove event!
		var _iDeltaX;
		var _iDeltaY;
		var _oDetails;

		var _aDict = ['zero','one','two','three','four'];

		// swipe marker
		var _bIsSwipe;


		// trigger events for all bound pseudo events on this element
		for (_sType in _oDatajQueryGestures) {

			// get current pseudo event
			_oEventData = _oDatajQueryGestures.oStartTouch;

			_oDelta = {};
			_iScreenX = (_bHasTouches) ? event_.changedTouches[0].screenX : event_.screenX;
			_iScreenY = (_bHasTouches) ? event_.changedTouches[0].screenY : event_.screenY;
			// calculate distances in relation to the touchstart position not the last touchmove event!
			_iDeltaX = _iScreenX - _oEventData.screenX ;
			_iDeltaY = _iScreenY - _oEventData.screenY;
			_oDetails = _createOptions({type: 'swipe', touches: _iTouches, screenY: _iScreenY,screenX:_iScreenX ,deltaY: _iDeltaY,deltaX : _iDeltaX, startMove:_oEventData, event:event_, timestamp:  _oEventData.timestamp });


			// swipe marker
			_bIsSwipe = false;

			// trigger bound events on this element
			switch(_sType) {
				case 'swipeone':

					if( _bHasTouches === false && _iTouches == 1 && _bHasMoved === false){
						// trigger tapone!
						break;
					}
					if (_bHasTouches===false || ( _iTouches == 1  && _bHasMoved === true && _bHasSwipeGesture===true)) {
						_bIsSwipe = true;

						_oDetails.type = ['swipe',_aDict[_iTouches]].join('');
						_$element.triggerHandler(_oDetails.type,_oDetails);
					}
				break;

				case 'swipetwo':
					if (( _bHasTouches && _iTouches== 2 && _bHasMoved === true && _bHasSwipeGesture===true)) {
						_bIsSwipe = true;
						_oDetails.type = ['swipe',_aDict[_iTouches]].join('');
						_$element.triggerHandler(_oDetails.type,_oDetails);
					}
				break;

				case 'swipethree':
					if ( ( _bHasTouches && _iTouches == 3 && _bHasMoved === true && _bHasSwipeGesture===true)) {
						_bIsSwipe = true;
						_oDetails.type = ['swipe',_aDict[_iTouches]].join('');
						_$element.triggerHandler(_oDetails.type,_oDetails);
					}
				break;

				case 'swipefour':
					if ( ( _bHasTouches && _iTouches == 4 && _bHasMoved === true && _bHasSwipeGesture===true)) {
						_bIsSwipe = true;
						_oDetails.type = ['swipe',_aDict[_iTouches]].join('');
						_$element.triggerHandler(_oDetails.type,_oDetails);
					}
				break;

				case 'swipeup':
				case 'swiperightup':
				case 'swiperight':
				case 'swiperightdown':
				case 'swipedown':
				case 'swipeleftdown':
				case 'swipeleft':
				case 'swipeleftup':
					if ( _bHasTouches && _bHasMoved === true && _bHasSwipeGesture===true) {
						_bIsSwipe = true;
						_oDetails.type = [
									'swipe',
								((_oDetails.delta[0].lastX != 0) ? ((_oDetails.delta[0].lastX > 0) ? 'right' : 'left') : ''),
								((_oDetails.delta[0].lastY != 0) ? ((_oDetails.delta[0].lastY > 0) ? 'down' : 'up') :'')
									].join('');
						_$element.triggerHandler(_oDetails.type, _oDetails);
					}
				break;

				case 'tapone':
				case 'taptwo':
				case 'tapthree':
				case 'tapfour':
					if (( /* _bHasTouches && */ _bHasMoved !== true && _bIsSwipe !==true) && (_aDict[_iTouches] ==_sType.slice(3)) ) {
						_oDetails.description = ['tap',_aDict[_iTouches]].join('');
						_oDetails.type = ['tap',_aDict[_iTouches]].join('');
						_$element.triggerHandler(_oDetails.type,_oDetails);
						}
					break;

			}

			// refresh pseudo events
			var _oObj = {};
//			_oObj[_sType] = false;
//			_oObj.hasTouchmoved = false;
			_$element.data('ojQueryGestures',$.extend(true,_oDatajQueryGestures,_oObj));
			_$element.data('ojQueryGestures',$.extend(true,_oDatajQueryGestures,_oObj));

		}
		_$element.triggerHandler($.jGestures.events.touchendProcessed,event_);
	}


	/**
	* Handler: gesturestart
	* Setup pseudo-event by storing initial values such as :
	*	timestamp: {Number}
	*  on the pseudo gesture event
	* Since the gesture-event doesn't supply event.touches no tuchpoints will be calculated
	* @param {DOM-Event} event_
	* @return {Void}
	*/
	function _onGesturestart(event_) {

		// ignore bubbled handlers
		// if ( event_.currentTarget !== event_.target ) { return; }

		var _$element = jQuery(event_.currentTarget);
		// var _$element = jQuery(event_.target);

		// trigger custom notification
		_$element.triggerHandler($.jGestures.events.gesturestart,event_);


		// get stored pseudo event
		var _oDatajQueryGestures = _$element.data('ojQueryGestures');

		// var _oEventData = _oDatajQueryGestures[_sType];
		// store current values for calculating relative values (changes between touchmoveevents)
		var _oObj = {};
		_oObj.oStartTouch = {timestamp:new Date().getTime()};
		_$element.data('ojQueryGestures',$.extend(true,_oDatajQueryGestures,_oObj));
	}

	/**
	* Handler: gesturechange
	* Read the event_.scale / event_.rotate values,
	* an triggers a pinch|rotate event if necessary.
	* Since the gesture-event doesn't supply event.touches no tuchpoints will be calculated
	* @returns {Object}
	*			{
	*				type: eventtype e.g. "pinch","rotate",
	*				originalEvent: {DOM-Event},
	*				// delta and direction details are just provided for touch not for gesture / motion events
	*				delta : null,
	*				direction : {
	*					vector: {Number}, // -1|+1, indicates the direction if necessary(pinch/rotate)
	*					 orientation: {Number} // window.orientation: -90,0,90,180 || null (window.orienntation)
	*				 },
	*				rotation: {Number} , //  amount of rotation relative to the current position NOT the original
	*				scale: {Number} , // amount of scaling relative to the current position NOT the original
	*				duration: {Number}, // ms: relative to the original touchpoint
	*				description : {String} // details as String: pinch:{'close'|'open'} e.g. "pinch:-1:close" ||  rotate:{'counterclockwise'|'clockwise'} e.g. "rotate:-1:counterclockwise"
	*			};
	* @param {DOM-Event} event_
	* @return {Void}
	*/
	function _onGesturechange(event_) {

		// ignore bubbled handlers
		// if ( event_.currentTarget !== event_.target ) { return; }

		var _$element = jQuery(event_.currentTarget);
		// var _$element = jQuery(event_.target);
		var _iDelta,_iDirection,_sDesc,_oDetails;
		// get all pseudo events
		var _oDatajQueryGestures = _$element.data('ojQueryGestures');

		// trigger events for all bound pseudo events on this element
		var _sType;
		for (_sType in _oDatajQueryGestures) {

			// trigger a specific bound event
			switch(_sType) {

				case 'pinch':
					_iDelta = event_.scale;
					if ( ( ( _iDelta < 1 ) && (_iDelta % 1) < (1 - $.jGestures.defaults.thresholdPinchclose) ) || ( ( _iDelta > 1 ) && (_iDelta % 1) > ($.jGestures.defaults.thresholdPinchopen) ) ) {
						_iDirection = (_iDelta < 1 ) ? -1 : +1 ;
						_oDetails = _createOptions({type: 'pinch', scale: _iDelta, touches: null,startMove:_oDatajQueryGestures.oStartTouch, event:event_, timestamp: _oDatajQueryGestures.oStartTouch.timestamp, vector:_iDirection, description: ['pinch:',_iDirection,':' , ( (_iDelta < 1 ) ? 'close' : 'open' )].join('') });
						_$element.triggerHandler(_oDetails.type, _oDetails);
					}
				break;

				case 'rotate':
					_iDelta = event_.rotation;
					if ( ( ( _iDelta < 1 ) &&  ( -1*(_iDelta) > $.jGestures.defaults.thresholdRotateccw ) ) || ( ( _iDelta > 1 ) && (_iDelta  > $.jGestures.defaults.thresholdRotatecw) ) ) {
						_iDirection = (_iDelta < 1 ) ? -1 : +1 ;
						_oDetails = _createOptions({type: 'rotate', rotation: _iDelta, touches: null, startMove:_oDatajQueryGestures.oStartTouch, event:event_, timestamp: _oDatajQueryGestures.oStartTouch.timestamp, vector:_iDirection, description: ['rotate:',_iDirection,':' , ( (_iDelta < 1 ) ? 'counterclockwise' : 'clockwise' )].join('') });
						_$element.triggerHandler(_oDetails.type, _oDetails);
					}
				break;

			}
		}

	}


	/**
	* Handler: gestureend
	* Read the event_.scale / event_.rotate values,
	* compares it to $.jGestures.defaults.threshold* and triggers
	* a pinchclose|pinchclose|rotatecw|rotateccw event if the distance exceed the
	* Since the gesture-event doesn't supply event.touches no tuchpoints will be calculated
	* * Custom-event argument object:
	* @returns {Object}
	*			{
	*				type: eventtype e.g. "pinchclose","pinchopen", "rotatecw", "rotateccw",
	*				originalEvent: {DOM-Event},
	*				// delta and direction details are just provided for touch not for gesture / motion events
	*				delta : null,
	*				// based on the first touchpoint
	*				direction : {
	*					vector: {Number}, // -1|+1, indicates the direction if necessary(pinch/rotate)
	*					orientation: {Number} // window.orientation: -90,0,90,180 || null (window.orienntation)
	*				},
	*				rotation: {Number} , //  amount of rotation relative to the current position NOT the original
	*				scale: {Number} , // amount of scaling relative to the current position NOT the original
	*				duration: {Number}, // ms: relative to the original touchpoint
	*				description : {String} // details as String: pinch:{'close'|'open'} e.g. "pinch:-1:close" ||  rotate:{'counterclockwise'|'clockwise'} e.g. "rotate:-1:counterclockwise"
	*			};
	* @param {DOM-Event} event_
	* @return {Void}
	*/
	function _onGestureend(event_) {
		// ignore bubbled handlers
		// if ( event_.currentTarget !== event_.target ) { return; }

		var _$element = jQuery(event_.currentTarget);
		// var _$element = jQuery(event_.target);

		// trigger custom notification
		_$element.triggerHandler($.jGestures.events.gestureendStart,event_);

		var _iDelta;
		var _oDatajQueryGestures = _$element.data('ojQueryGestures');

		// trigger handler for every bound event
		var _sType;
		for (_sType in _oDatajQueryGestures) {

			switch(_sType) {

				case 'pinchclose':
					_iDelta = event_.scale;
					if (( _iDelta < 1 ) && (_iDelta % 1) < (1 - $.jGestures.defaults.thresholdPinchclose)) {
						_$element.triggerHandler('pinchclose', _createOptions ({type: 'pinchclose', scale:_iDelta, vector: -1, touches: null, startMove: _oDatajQueryGestures.oStartTouch, event:event_, timestamp:_oDatajQueryGestures.oStartTouch.timestamp,description: 'pinch:-1:close' }) );
					}
				break;

				case 'pinchopen':
					_iDelta = event_.scale;
					if ( ( _iDelta > 1 ) && (_iDelta % 1) > ($.jGestures.defaults.thresholdPinchopen) ) {
						_$element.triggerHandler('pinchopen', _createOptions ({type: 'pinchopen', scale:_iDelta, vector: +1, touches: null, startMove: _oDatajQueryGestures.oStartTouch, event:event_, timestamp:_oDatajQueryGestures.oStartTouch.timestamp,description: 'pinch:+1:open'}) );
					}
				break;

				case 'rotatecw':
					_iDelta = event_.rotation;
					if ( ( _iDelta > 1 ) && (_iDelta  > $.jGestures.defaults.thresholdRotatecw) ) {
						_$element.triggerHandler('rotatecw', _createOptions ({type: 'rotatecw', rotation:_iDelta, vector: +1, touches: null, startMove: _oDatajQueryGestures.oStartTouch, event:event_, timestamp:_oDatajQueryGestures.oStartTouch.timestamp,description: 'rotate:+1:clockwise'}) );
					}
				break;

				case 'rotateccw':
					_iDelta = event_.rotation;
					if ( ( _iDelta < 1 ) &&  ( -1*(_iDelta) > $.jGestures.defaults.thresholdRotateccw ) ) {
							_$element.triggerHandler('rotateccw', _createOptions ({type: 'rotateccw', rotation:_iDelta, vector: -1, touches: null, startMove: _oDatajQueryGestures.oStartTouch, event:event_, timestamp:_oDatajQueryGestures.oStartTouch.timestamp,description: 'rotate:-1:counterclockwise'}) );
						}
				break;

				}
			}
			_$element.triggerHandler($.jGestures.events.gestureendProcessed,event_);
		}
	}
)(jQuery);
