'use strict';

var config = {
	yAxisWidth: 15,
	yAxisSplit: 5,
	xAxisHeight: 15,
	xAxisLineHeight: 15,
	legendHeight: 15,
	yAxisTitleWidth: 15,
	padding: [10, 10, 10, 10],
	pixelRatio: 1,
	rotate: false,
	columePadding: 3,
	fontSize: 13,
	//dataPointShape: ['diamond', 'circle', 'triangle', 'rect'],
	dataPointShape: ['circle', 'circle', 'circle', 'circle'],
	colors: ['#1890ff', '#2fc25b', '#facc14', '#f04864', '#8543e0', '#90ed7d'],
	pieChartLinePadding: 15,
	pieChartTextPadding: 5,
	xAxisTextPadding: 3,
	titleColor: '#333333',
	titleFontSize: 20,
	subtitleColor: '#999999',
	subtitleFontSize: 15,
	toolTipPadding: 3,
	toolTipBackground: '#000000',
	toolTipOpacity: 0.7,
	toolTipLineHeight: 20,
	radarLabelTextMargin: 15,
	gaugeLabelTextMargin: 15
};

let assign = function(target, ...varArgs) {
	if (target == null) {
		throw new TypeError('Cannot convert undefined or null to object');
	}
	if (!varArgs || varArgs.length <= 0) {
		return target;
	}
	// 深度合并对象
	function deepAssign(obj1, obj2) {
		for (let key in obj2) {
			obj1[key] = obj1[key] && obj1[key].toString() === "[object Object]" ?
				deepAssign(obj1[key], obj2[key]) : obj1[key] = obj2[key];
		}
		return obj1;
	}

	varArgs.forEach(val => {
		target = deepAssign(target, val);
	});
	return target;
};

var util = {
	toFixed: function toFixed(num, limit) {
		limit = limit || 2;
		if (this.isFloat(num)) {
			num = num.toFixed(limit);
		}
		return num;
	},
	isFloat: function isFloat(num) {
		return num % 1 !== 0;
	},
	approximatelyEqual: function approximatelyEqual(num1, num2) {
		return Math.abs(num1 - num2) < 1e-10;
	},
	isSameSign: function isSameSign(num1, num2) {
		return Math.abs(num1) === num1 && Math.abs(num2) === num2 || Math.abs(num1) !== num1 && Math.abs(
			num2) !== num2;
	},
	isSameXCoordinateArea: function isSameXCoordinateArea(p1, p2) {
		return this.isSameSign(p1.x, p2.x);
	},
	isCollision: function isCollision(obj1, obj2) {
		obj1.end = {};
		obj1.end.x = obj1.start.x + obj1.width;
		obj1.end.y = obj1.start.y - obj1.height;
		obj2.end = {};
		obj2.end.x = obj2.start.x + obj2.width;
		obj2.end.y = obj2.start.y - obj2.height;
		var flag = obj2.start.x > obj1.end.x || obj2.end.x < obj1.start.x || obj2.end.y > obj1.start.y || obj2
			.start.y < obj1.end.y;
		return !flag;
	}
};

//兼容H5点击事件
function getH5Offset(e) {
	e.mp = {
		changedTouches: []
	};
	e.mp.changedTouches.push({
		x: e.offsetX,
		y: e.offsetY
	});
	return e;
}

// hex 转 rgba
function hexToRgb(hexValue, opc) {
	var rgx = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
	var hex = hexValue.replace(rgx, function(m, r, g, b) {
		return r + r + g + g + b + b;
	});
	var rgb = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
	var r = parseInt(rgb[1], 16);
	var g = parseInt(rgb[2], 16);
	var b = parseInt(rgb[3], 16);
	return 'rgba(' + r + ',' + g + ',' + b + ',' + opc + ')';
}

function findRange(num, type, limit) {
	if (isNaN(num)) {
		throw new Error('[uCharts] unvalid series data!');
	}
	limit = limit || 10;
	type = type ? type : 'upper';
	var multiple = 1;
	while (limit < 1) {
		limit *= 10;
		multiple *= 10;
	}
	if (type === 'upper') {
		num = Math.ceil(num * multiple);
	} else {
		num = Math.floor(num * multiple);
	}
	while (num % limit !== 0) {
		if (type === 'upper') {
			num++;
		} else {
			num--;
		}
	}
	return num / multiple;
}

function calCandleMA(dayArr, nameArr, colorArr, kdata) {
	let seriesTemp = [];
	for (let k = 0; k < dayArr.length; k++) {
		let seriesItem = {
			data: [],
			name: nameArr[k],
			color: colorArr[k]
		};
		for (let i = 0, len = kdata.length; i < len; i++) {
			if (i < dayArr[k]) {
				seriesItem.data.push(null);
				continue;
			}
			let sum = 0;
			for (let j = 0; j < dayArr[k]; j++) {
				sum += kdata[i - j][1];
			}
			seriesItem.data.push(+(sum / dayArr[k]).toFixed(3));
		}
		seriesTemp.push(seriesItem);
	}
	return seriesTemp;
}

function calValidDistance(self, distance, chartData, config, opts) {
	var dataChartAreaWidth = opts.width - opts.area[1] - opts.area[3];
	var dataChartWidth = chartData.eachSpacing * (opts.chartData.xAxisData.xAxisPoints.length - 1);
	var validDistance = distance;
	if (distance >= 0) {
		validDistance = 0;
		self.event.trigger('scrollLeft');
	} else if (Math.abs(distance) >= dataChartWidth - dataChartAreaWidth) {
		validDistance = dataChartAreaWidth - dataChartWidth;
		self.event.trigger('scrollRight');
	}
	return validDistance;
}

function isInAngleRange(angle, startAngle, endAngle) {
	function adjust(angle) {
		while (angle < 0) {
			angle += 2 * Math.PI;
		}
		while (angle > 2 * Math.PI) {
			angle -= 2 * Math.PI;
		}
		return angle;
	}
	angle = adjust(angle);
	startAngle = adjust(startAngle);
	endAngle = adjust(endAngle);
	if (startAngle > endAngle) {
		endAngle += 2 * Math.PI;
		if (angle < startAngle) {
			angle += 2 * Math.PI;
		}
	}
	return angle >= startAngle && angle <= endAngle;
}

function calRotateTranslate(x, y, h) {
	var xv = x;
	var yv = h - y;
	var transX = xv + (h - yv - xv) / Math.sqrt(2);
	transX *= -1;
	var transY = (h - yv) * (Math.sqrt(2) - 1) - (h - yv - xv) / Math.sqrt(2);
	return {
		transX: transX,
		transY: transY
	};
}

function createCurveControlPoints(points, i) {

	function isNotMiddlePoint(points, i) {
		if (points[i - 1] && points[i + 1]) {
			return points[i].y >= Math.max(points[i - 1].y, points[i + 1].y) || points[i].y <= Math.min(points[i - 1].y,
				points[i + 1].y);
		} else {
			return false;
		}
	}

	function isNotMiddlePointX(points, i) {
		if (points[i - 1] && points[i + 1]) {
			return points[i].x >= Math.max(points[i - 1].x, points[i + 1].x) || points[i].x <= Math.min(points[i - 1].x,
				points[i + 1].x);
		} else {
			return false;
		}
	}
	var a = 0.2;
	var b = 0.2;
	var pAx = null;
	var pAy = null;
	var pBx = null;
	var pBy = null;
	if (i < 1) {
		pAx = points[0].x + (points[1].x - points[0].x) * a;
		pAy = points[0].y + (points[1].y - points[0].y) * a;
	} else {
		pAx = points[i].x + (points[i + 1].x - points[i - 1].x) * a;
		pAy = points[i].y + (points[i + 1].y - points[i - 1].y) * a;
	}

	if (i > points.length - 3) {
		var last = points.length - 1;
		pBx = points[last].x - (points[last].x - points[last - 1].x) * b;
		pBy = points[last].y - (points[last].y - points[last - 1].y) * b;
	} else {
		pBx = points[i + 1].x - (points[i + 2].x - points[i].x) * b;
		pBy = points[i + 1].y - (points[i + 2].y - points[i].y) * b;
	}
	if (isNotMiddlePoint(points, i + 1)) {
		pBy = points[i + 1].y;
	}
	if (isNotMiddlePoint(points, i)) {
		pAy = points[i].y;
	}
	if (isNotMiddlePointX(points, i + 1)) {
		pBx = points[i + 1].x;
	}
	if (isNotMiddlePointX(points, i)) {
		pAx = points[i].x;
	}
	if (pAy >= Math.max(points[i].y, points[i + 1].y) || pAy <= Math.min(points[i].y, points[i + 1].y)) {
		pAy = points[i].y;
	}
	if (pBy >= Math.max(points[i].y, points[i + 1].y) || pBy <= Math.min(points[i].y, points[i + 1].y)) {
		pBy = points[i + 1].y;
	}
	if (pAx >= Math.max(points[i].x, points[i + 1].x) || pAx <= Math.min(points[i].x, points[i + 1].x)) {
		pAx = points[i].x;
	}
	if (pBx >= Math.max(points[i].x, points[i + 1].x) || pBx <= Math.min(points[i].x, points[i + 1].x)) {
		pBx = points[i + 1].x;
	}
	return {
		ctrA: {
			x: pAx,
			y: pAy
		},
		ctrB: {
			x: pBx,
			y: pBy
		}
	};
}

function convertCoordinateOrigin(x, y, center) {
	return {
		x: center.x + x,
		y: center.y - y
	};
}

function avoidCollision(obj, target) {
	if (target) {
		// is collision test
		while (util.isCollision(obj, target)) {
			if (obj.start.x > 0) {
				obj.start.y--;
			} else if (obj.start.x < 0) {
				obj.start.y++;
			} else {
				if (obj.start.y > 0) {
					obj.start.y++;
				} else {
					obj.start.y--;
				}
			}
		}
	}
	return obj;
}

function fillSeries(series, opts, config) {
	var index = 0;
	return series.map(function(item) {
		if (!item.color) {
			item.color = config.colors[index];
			index = (index + 1) % config.colors.length;
		}
		if (!item.index) {
			item.index = 0;
		}
		if (!item.type) {
			item.type = opts.type;
		}
		if (typeof item.show == "undefined") {
			item.show = true;
		}
		if (!item.type) {
			item.type = opts.type;
		}
		if (!item.pointShape) {
			item.pointShape = "circle";
		}
		if (!item.legendShape) {
			switch (item.type) {
				case 'line':
					item.legendShape = "line";
					break;
				case 'column':
					item.legendShape = "rect";
					break;
				case 'area':
					item.legendShape = "triangle";
					break;
				default:
					item.legendShape = "circle";
			}
		}
		return item;
	});
}

function getDataRange(minData, maxData) {
	var limit = 0;
	var range = maxData - minData;
	if (range >= 10000) {
		limit = 1000;
	} else if (range >= 1000) {
		limit = 100;
	} else if (range >= 100) {
		limit = 10;
	} else if (range >= 10) {
		limit = 5;
	} else if (range >= 1) {
		limit = 1;
	} else if (range >= 0.1) {
		limit = 0.1;
	} else if (range >= 0.01) {
		limit = 0.01;
	} else if (range >= 0.001) {
		limit = 0.001;
	} else if (range >= 0.0001) {
		limit = 0.0001;
	} else if (range >= 0.00001) {
		limit = 0.00001;
	} else {
		limit = 0.000001;
	}
	return {
		minRange: findRange(minData, 'lower', limit),
		maxRange: findRange(maxData, 'upper', limit)
	};
}

function measureText(text) {
	var fontSize = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : config.fontSize;
	text = String(text);
	var text = text.split('');
	var width = 0;
	for (let i = 0; i < text.length; i++) {
		let item = text[i];
		if (/[a-zA-Z]/.test(item)) {
			width += 7;
		} else if (/[0-9]/.test(item)) {
			width += 5.5;
		} else if (/\./.test(item)) {
			width += 2.7;
		} else if (/-/.test(item)) {
			width += 3.25;
		} else if (/[\u4e00-\u9fa5]/.test(item)) {
			width += 10;
		} else if (/\(|\)/.test(item)) {
			width += 3.73;
		} else if (/\s/.test(item)) {
			width += 2.5;
		} else if (/%/.test(item)) {
			width += 8;
		} else {
			width += 10;
		}
	}
	return width * fontSize / 10;
}

function dataCombine(series) {
	return series.reduce(function(a, b) {
		return (a.data ? a.data : a).concat(b.data);
	}, []);
}

function dataCombineStack(series, len) {
	var sum = new Array(len);
	for (var j = 0; j < sum.length; j++) {
		sum[j] = 0;
	}
	for (var i = 0; i < series.length; i++) {
		for (var j = 0; j < sum.length; j++) {
			sum[j] += series[i].data[j];
		}
	}
	return series.reduce(function(a, b) {
		return (a.data ? a.data : a).concat(b.data).concat(sum);
	}, []);
}

function getTouches(touches, opts, e) {
	let x, y;
	if (touches.clientX) {
		if (opts.rotate) {
			y = opts.height - touches.clientX * opts.pixelRatio;
			x = (touches.pageY - e.currentTarget.offsetTop - (opts.height / opts.pixelRatio / 2) * (opts.pixelRatio -
					1)) *
				opts.pixelRatio;
		} else {
			x = touches.clientX * opts.pixelRatio;
			y = (touches.pageY - e.currentTarget.offsetTop - (opts.height / opts.pixelRatio / 2) * (opts.pixelRatio -
					1)) *
				opts.pixelRatio;
		}
	} else {
		if (opts.rotate) {
			y = opts.height - touches.x * opts.pixelRatio;
			x = touches.y * opts.pixelRatio;
		} else {
			x = touches.x * opts.pixelRatio;
			y = touches.y * opts.pixelRatio;
		}
	}
	return {
		x: x,
		y: y
	}
}

function getSeriesDataItem(series, index) {
	var data = [];
	for (let i = 0; i < series.length; i++) {
		let item = series[i];
		if (item.data[index] !== null && typeof item.data[index] !== 'undefined' && item.show) {
			let seriesItem = {};
			seriesItem.color = item.color;
			seriesItem.type = item.type;
			seriesItem.style = item.style;
			seriesItem.pointShape = item.pointShape;
			seriesItem.disableLegend = item.disableLegend;
			seriesItem.name = item.name;
			seriesItem.show = item.show;
			seriesItem.data = item.format ? item.format(item.data[index]) : item.data[index];
			data.push(seriesItem);
		}
	}
	return data;
}

function getMaxTextListLength(list) {
	var lengthList = list.map(function(item) {
		return measureText(item);
	});
	return Math.max.apply(null, lengthList);
}

function getRadarCoordinateSeries(length) {
	var eachAngle = 2 * Math.PI / length;
	var CoordinateSeries = [];
	for (var i = 0; i < length; i++) {
		CoordinateSeries.push(eachAngle * i);
	}

	return CoordinateSeries.map(function(item) {
		return -1 * item + Math.PI / 2;
	});
}

function getToolTipData(seriesData, calPoints, index, categories) {
	var option = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : {};

	var textList = seriesData.map(function(item) {
		let titleText = [];
		if (categories) {
			titleText = categories;
		} else {
			titleText = item.data;
		}
		return {
			text: option.format ? option.format(item, titleText[index]) : item.name + ': ' + item.data,
			color: item.color
		};
	});
	var validCalPoints = [];
	var offset = {
		x: 0,
		y: 0
	};
	for (let i = 0; i < calPoints.length; i++) {
		let points = calPoints[i];
		if (typeof points[index] !== 'undefined' && points[index] !== null) {
			validCalPoints.push(points[index]);
		}
	}
	for (let i = 0; i < validCalPoints.length; i++) {
		let item = validCalPoints[i];
		offset.x = Math.round(item.x);
		offset.y += item.y;
	}
	offset.y /= validCalPoints.length;
	return {
		textList: textList,
		offset: offset
	};
}

function getMixToolTipData(seriesData, calPoints, index, categories) {
	var option = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : {};
	var textList = seriesData.map(function(item) {
		return {
			text: option.format ? option.format(item, categories[index]) : item.name + ': ' + item.data,
			color: item.color,
			disableLegend: item.disableLegend ? true : false
		};
	});
	textList = textList.filter(function(item) {
		if (item.disableLegend !== true) {
			return item;
		}
	});
	var validCalPoints = [];
	var offset = {
		x: 0,
		y: 0
	};
	for (let i = 0; i < calPoints.length; i++) {
		let points = calPoints[i];
		if (typeof points[index] !== 'undefined' && points[index] !== null) {
			validCalPoints.push(points[index]);
		}
	}
	for (let i = 0; i < validCalPoints.length; i++) {
		let item = validCalPoints[i];
		offset.x = Math.round(item.x);
		offset.y += item.y;
	}
	offset.y /= validCalPoints.length;
	return {
		textList: textList,
		offset: offset
	};
}

function getCandleToolTipData(series, seriesData, calPoints, index, categories, extra) {
	var option = arguments.length > 6 && arguments[6] !== undefined ? arguments[6] : {};
	let upColor = extra.color.upFill;
	let downColor = extra.color.downFill;
	//颜色顺序为开盘，收盘，最低，最高
	let color = [upColor, upColor, downColor, upColor];
	var textList = [];
	let text0 = {
		text: categories[index],
		color: null
	};
	textList.push(text0);
	seriesData.map(function(item) {
		if (index == 0 && item.data[1] - item.data[0] < 0) {
			color[1] = downColor;
		} else {
			if (item.data[0] < series[index - 1][1]) {
				color[0] = downColor;
			}
			if (item.data[1] < item.data[0]) {
				color[1] = downColor;
			}
			if (item.data[2] > series[index - 1][1]) {
				color[2] = upColor;
			}
			if (item.data[3] < series[index - 1][1]) {
				color[3] = downColor;
			}
		}
		let text1 = {
			text: '开盘：' + item.data[0],
			color: color[0]
		};
		let text2 = {
			text: '收盘：' + item.data[1],
			color: color[1]
		};
		let text3 = {
			text: '最低：' + item.data[2],
			color: color[2]
		};
		let text4 = {
			text: '最高：' + item.data[3],
			color: color[3]
		};
		textList.push(text1, text2, text3, text4);
	});
	var validCalPoints = [];
	var offset = {
		x: 0,
		y: 0
	};
	for (let i = 0; i < calPoints.length; i++) {
		let points = calPoints[i];
		if (typeof points[index] !== 'undefined' && points[index] !== null) {
			validCalPoints.push(points[index]);
		}
	}
	offset.x = Math.round(validCalPoints[0][0].x);
	return {
		textList: textList,
		offset: offset
	};
}

function filterSeries(series) {
	let tempSeries = [];
	for (let i = 0; i < series.length; i++) {
		if (series[i].show == true) {
			tempSeries.push(series[i])
		}
	}
	return tempSeries;
}

function findCurrentIndex(currentPoints, calPoints, opts, config) {
	var offset = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 0;
	var currentIndex = -1;
	var spacing = opts.chartData.eachSpacing / 2;
	let xAxisPoints = [];
	if (calPoints.length > 0) {
		if (opts.type == 'candle') {
			for (let i = 0; i < calPoints[0].length; i++) {
				xAxisPoints.push(calPoints[0][i][0].x)
			}
		} else {
			for (let i = 0; i < calPoints[0].length; i++) {
				xAxisPoints.push(calPoints[0][i].x)
			}
		}
		if ((opts.type == 'line' || opts.type == 'area') && opts.xAxis.boundaryGap == 'justify') {
			spacing = opts.chartData.eachSpacing / 2;
		}
		if (!opts.categories) {
			spacing = 0
		}
		if (isInExactChartArea(currentPoints, opts, config)) {
			xAxisPoints.forEach(function(item, index) {
				if (currentPoints.x + offset + spacing > item) {
					currentIndex = index;
				}
			});
		}
	}
	return currentIndex;
}

function findLegendIndex(currentPoints, legendData, opts) {
	let currentIndex = -1;
	if (isInExactLegendArea(currentPoints, legendData.area)) {
		let points = legendData.points;
		let index = -1;
		for (let i = 0, len = points.length; i < len; i++) {
			let item = points[i];
			for (let j = 0; j < item.length; j++) {
				index += 1;
				let area = item[j]['area'];
				if (currentPoints.x > area[0] && currentPoints.x < area[2] && currentPoints.y > area[1] && currentPoints
					.y < area[3]) {
					currentIndex = index;
					break;
				}
			}
		}
		return currentIndex;
	}
	return currentIndex;
}

function isInExactLegendArea(currentPoints, area) {
	return currentPoints.x > area.start.x && currentPoints.x < area.end.x && currentPoints.y > area.start.y &&
		currentPoints.y < area.end.y;
}

function isInExactChartArea(currentPoints, opts, config) {
	return currentPoints.x <= opts.width - opts.area[1] + 10 && currentPoints.x >= opts.area[3] - 10 && currentPoints
		.y >= opts.area[0] && currentPoints.y <= opts.height - opts.area[2];
}

function findRadarChartCurrentIndex(currentPoints, radarData, count) {
	var eachAngleArea = 2 * Math.PI / count;
	var currentIndex = -1;
	if (isInExactPieChartArea(currentPoints, radarData.center, radarData.radius)) {
		var fixAngle = function fixAngle(angle) {
			if (angle < 0) {
				angle += 2 * Math.PI;
			}
			if (angle > 2 * Math.PI) {
				angle -= 2 * Math.PI;
			}
			return angle;
		};

		var angle = Math.atan2(radarData.center.y - currentPoints.y, currentPoints.x - radarData.center.x);
		angle = -1 * angle;
		if (angle < 0) {
			angle += 2 * Math.PI;
		}

		var angleList = radarData.angleList.map(function(item) {
			item = fixAngle(-1 * item);

			return item;
		});

		angleList.forEach(function(item, index) {
			var rangeStart = fixAngle(item - eachAngleArea / 2);
			var rangeEnd = fixAngle(item + eachAngleArea / 2);
			if (rangeEnd < rangeStart) {
				rangeEnd += 2 * Math.PI;
			}
			if (angle >= rangeStart && angle <= rangeEnd || angle + 2 * Math.PI >= rangeStart && angle + 2 *
				Math.PI <=
				rangeEnd) {
				currentIndex = index;
			}
		});
	}

	return currentIndex;
}

function findFunnelChartCurrentIndex(currentPoints, funnelData) {
	var currentIndex = -1;
	for (var i = 0, len = funnelData.series.length; i < len; i++) {
		var item = funnelData.series[i];
		if (currentPoints.x > item.funnelArea[0] && currentPoints.x < item.funnelArea[2] && currentPoints.y > item
			.funnelArea[1] && currentPoints.y < item.funnelArea[3]) {
			currentIndex = i;
			break;
		}
	}
	return currentIndex;
}

function findWordChartCurrentIndex(currentPoints, wordData) {
	var currentIndex = -1;
	for (var i = 0, len = wordData.length; i < len; i++) {
		var item = wordData[i];
		if (currentPoints.x > item.area[0] && currentPoints.x < item.area[2] && currentPoints.y > item.area[1] &&
			currentPoints.y < item.area[3]) {
			currentIndex = i;
			break;
		}
	}
	return currentIndex;
}

function findMapChartCurrentIndex(currentPoints, opts) {
	var currentIndex = -1;
	var cData = opts.chartData.mapData;
	var data = opts.series;
	var tmp = pointToCoordinate(currentPoints.y, currentPoints.x, cData.bounds, cData.scale, cData.xoffset, cData
		.yoffset);
	var poi = [tmp.x, tmp.y];
	for (var i = 0, len = data.length; i < len; i++) {
		var item = data[i].geometry.coordinates;
		if (isPoiWithinPoly(poi, item)) {
			currentIndex = i;
			break;
		}
	}
	return currentIndex;
}

function findPieChartCurrentIndex(currentPoints, pieData) {
	var currentIndex = -1;
	if (isInExactPieChartArea(currentPoints, pieData.center, pieData.radius)) {
		var angle = Math.atan2(pieData.center.y - currentPoints.y, currentPoints.x - pieData.center.x);
		angle = -angle;
		for (var i = 0, len = pieData.series.length; i < len; i++) {
			var item = pieData.series[i];
			if (isInAngleRange(angle, item._start_, item._start_ + item._proportion_ * 2 * Math.PI)) {
				currentIndex = i;
				break;
			}
		}
	}

	return currentIndex;
}

function isInExactPieChartArea(currentPoints, center, radius) {
	return Math.pow(currentPoints.x - center.x, 2) + Math.pow(currentPoints.y - center.y, 2) <= Math.pow(radius, 2);
}

function splitPoints(points) {
	var newPoints = [];
	var items = [];
	points.forEach(function(item, index) {
		if (item !== null) {
			items.push(item);
		} else {
			if (items.length) {
				newPoints.push(items);
			}
			items = [];
		}
	});
	if (items.length) {
		newPoints.push(items);
	}

	return newPoints;
}

function calLegendData(series, opts, config, chartData) {
	let legendData = {
		area: {
			start: {
				x: 0,
				y: 0
			},
			end: {
				x: 0,
				y: 0
			},
			width: 0,
			height: 0,
			wholeWidth: 0,
			wholeHeight: 0
		},
		points: [],
		widthArr: [],
		heightArr: []
	};
	if (opts.legend.show === false) {
		chartData.legendData = legendData;
		return legendData;
	}

	let padding = opts.legend.padding;
	let margin = opts.legend.margin;
	let fontSize = opts.legend.fontSize;
	let shapeWidth = 15 * opts.pixelRatio;
	let shapeRight = 5 * opts.pixelRatio;
	let lineHeight = Math.max(opts.legend.lineHeight * opts.pixelRatio, fontSize);
	if (opts.legend.position == 'top' || opts.legend.position == 'bottom') {
		let legendList = [];
		let widthCount = 0;
		let widthCountArr = [];
		let currentRow = [];
		for (let i = 0; i < series.length; i++) {
			let item = series[i];
			let itemWidth = shapeWidth + shapeRight + measureText(item.name || 'undefined', fontSize) + opts.legend
				.itemGap;
			if (widthCount + itemWidth > opts.width - opts.padding[1] - opts.padding[3]) {
				legendList.push(currentRow);
				widthCountArr.push(widthCount - opts.legend.itemGap);
				widthCount = itemWidth;
				currentRow = [item];
			} else {
				widthCount += itemWidth;
				currentRow.push(item);
			}
		}
		if (currentRow.length) {
			legendList.push(currentRow);
			widthCountArr.push(widthCount - opts.legend.itemGap);
			legendData.widthArr = widthCountArr;
			let legendWidth = Math.max.apply(null, widthCountArr);
			switch (opts.legend.float) {
				case 'left':
					legendData.area.start.x = opts.padding[3];
					legendData.area.end.x = opts.padding[3] + 2 * padding;
					break;
				case 'right':
					legendData.area.start.x = opts.width - opts.padding[1] - legendWidth - 2 * padding;
					legendData.area.end.x = opts.width - opts.padding[1];
					break;
				default:
					legendData.area.start.x = (opts.width - legendWidth) / 2 - padding;
					legendData.area.end.x = (opts.width + legendWidth) / 2 + padding;
			}
			legendData.area.width = legendWidth + 2 * padding;
			legendData.area.wholeWidth = legendWidth + 2 * padding;
			legendData.area.height = legendList.length * lineHeight + 2 * padding;
			legendData.area.wholeHeight = legendList.length * lineHeight + 2 * padding + 2 * margin;
			legendData.points = legendList;
		}
	} else {
		let len = series.length;
		let maxHeight = opts.height - opts.padding[0] - opts.padding[2] - 2 * margin - 2 * padding;
		let maxLength = Math.min(Math.floor(maxHeight / lineHeight), len);
		legendData.area.height = maxLength * lineHeight + padding * 2;
		legendData.area.wholeHeight = maxLength * lineHeight + padding * 2;
		switch (opts.legend.float) {
			case 'top':
				legendData.area.start.y = opts.padding[0] + margin;
				legendData.area.end.y = opts.padding[0] + margin + legendData.area.height;
				break;
			case 'bottom':
				legendData.area.start.y = opts.height - opts.padding[2] - margin - legendData.area.height;
				legendData.area.end.y = opts.height - opts.padding[2] - margin;
				break;
			default:
				legendData.area.start.y = (opts.height - legendData.area.height) / 2;
				legendData.area.end.y = (opts.height + legendData.area.height) / 2;
		}
		let lineNum = len % maxLength === 0 ? len / maxLength : Math.floor((len / maxLength) + 1);
		let currentRow = [];
		for (let i = 0; i < lineNum; i++) {
			let temp = series.slice(i * maxLength, i * maxLength + maxLength);
			currentRow.push(temp);
		}

		legendData.points = currentRow;

		if (currentRow.length) {
			for (let i = 0; i < currentRow.length; i++) {
				let item = currentRow[i];
				let maxWidth = 0;
				for (let j = 0; j < item.length; j++) {
					let itemWidth = shapeWidth + shapeRight + measureText(item[j].name || 'undefined', fontSize) + opts
						.legend.itemGap;
					if (itemWidth > maxWidth) {
						maxWidth = itemWidth;
					}
				}
				legendData.widthArr.push(maxWidth);
				legendData.heightArr.push(item.length * lineHeight + padding * 2);
			}
			let legendWidth = 0
			for (let i = 0; i < legendData.widthArr.length; i++) {
				legendWidth += legendData.widthArr[i];
			}
			legendData.area.width = legendWidth - opts.legend.itemGap + 2 * padding;
			legendData.area.wholeWidth = legendData.area.width + padding;
		}
	}

	switch (opts.legend.position) {
		case 'top':
			legendData.area.start.y = opts.padding[0] + margin;
			legendData.area.end.y = opts.padding[0] + margin + legendData.area.height;
			break;
		case 'bottom':
			legendData.area.start.y = opts.height - opts.padding[2] - legendData.area.height - margin;
			legendData.area.end.y = opts.height - opts.padding[2] - margin;
			break;
		case 'left':
			legendData.area.start.x = opts.padding[3];
			legendData.area.end.x = opts.padding[3] + legendData.area.width;
			break;
		case 'right':
			legendData.area.start.x = opts.width - opts.padding[1] - legendData.area.width;
			legendData.area.end.x = opts.width - opts.padding[1];
			break;
	}
	chartData.legendData = legendData;
	return legendData;
}

function calCategoriesData(categories, opts, config, eachSpacing) {
	var result = {
		angle: 0,
		xAxisHeight: config.xAxisHeight
	};
	var categoriesTextLenth = categories.map(function(item) {
		return measureText(item, opts.xAxis.fontSize || config.fontSize);
	});
	var maxTextLength = Math.max.apply(this, categoriesTextLenth);

	if (opts.xAxis.rotateLabel == true && maxTextLength + 2 * config.xAxisTextPadding > eachSpacing) {
		result.angle = 45 * Math.PI / 180;
		result.xAxisHeight = 2 * config.xAxisTextPadding + maxTextLength * Math.sin(result.angle);
	}
	return result;
}

function getXAxisTextList(series, opts, config) {
	var index = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : -1;
	var data = dataCombine(series);
	var sorted = [];
	// remove null from data
	data = data.filter(function(item) {
		//return item !== null;
		if (typeof item === 'object' && item !== null) {
			if (item.constructor == Array) {
				return item !== null;
			} else {
				return item.value !== null;
			}
		} else {
			return item !== null;
		}
	});
	data.map(function(item) {
		if (typeof item === 'object') {
			if (item.constructor == Array) {
				if (opts.type == 'candle') {
					item.map(function(subitem) {
						sorted.push(subitem);
					})
				} else {
					sorted.push(item[0]);
				}
			} else {
				sorted.push(item.value);
			}
		} else {
			sorted.push(item);
		}
	})

	var minData = 0;
	var maxData = 0;
	if (sorted.length > 0) {
		minData = Math.min.apply(this, sorted);
		maxData = Math.max.apply(this, sorted);
	}
	//为了兼容v1.9.0之前的项目
	if (index > -1) {
		if (typeof opts.xAxis.data[index].min === 'number') {
			minData = Math.min(opts.xAxis.data[index].min, minData);
		}
		if (typeof opts.xAxis.data[index].max === 'number') {
			maxData = Math.max(opts.xAxis.data[index].max, maxData);
		}
	} else {
		if (typeof opts.xAxis.min === 'number') {
			minData = Math.min(opts.xAxis.min, minData);
		}
		if (typeof opts.xAxis.max === 'number') {
			maxData = Math.max(opts.xAxis.max, maxData);
		}
	}


	if (minData === maxData) {
		var rangeSpan = maxData || 10;
		maxData += rangeSpan;
	}

	//var dataRange = getDataRange(minData, maxData);
	var minRange = minData;
	var maxRange = maxData;

	var range = [];
	var eachRange = (maxRange - minRange) / opts.xAxis.splitNumber;

	for (var i = 0; i <= opts.xAxis.splitNumber; i++) {
		range.push(minRange + eachRange * i);
	}
	return range;
}

function calXAxisData(series, opts, config) {
	var result = {
		angle: 0,
		xAxisHeight: config.xAxisHeight
	};

	result.ranges = getXAxisTextList(series, opts, config);
	result.rangesFormat = result.ranges.map(function(item) {
		item = opts.xAxis.format ? opts.xAxis.format(item) : util.toFixed(item, 2);
		return item;
	});

	var xAxisScaleValues = result.ranges.map(function(item) {
		// 如果刻度值是浮点数,则保留两位小数
		item = util.toFixed(item, 2);
		// 若有自定义格式则调用自定义的格式化函数
		item = opts.xAxis.format ? opts.xAxis.format(Number(item)) : item;
		return item;
	});

	result = Object.assign(result, getXAxisPoints(xAxisScaleValues, opts, config));
	// 计算X轴刻度的属性譬如每个刻度的间隔,刻度的起始点\结束点以及总长
	var eachSpacing = result.eachSpacing;

	var textLength = xAxisScaleValues.map(function(item) {
		return measureText(item);
	});

	// get max length of categories text
	var maxTextLength = Math.max.apply(this, textLength);

	// 如果刻度值文本内容过长,则将其逆时针旋转45°
	if (maxTextLength + 2 * config.xAxisTextPadding > eachSpacing) {
		result.angle = 45 * Math.PI / 180;
		result.xAxisHeight = 2 * config.xAxisTextPadding + maxTextLength * Math.sin(result.angle);
	}

	if (opts.xAxis.disabled === true) {
		result.xAxisHeight = 0;
	}

	return result;
}

function getRadarDataPoints(angleList, center, radius, series, opts) {
	var process = arguments.length > 5 && arguments[5] !== undefined ? arguments[5] : 1;

	var radarOption = opts.extra.radar || {};
	radarOption.max = radarOption.max || 0;
	var maxData = Math.max(radarOption.max, Math.max.apply(null, dataCombine(series)));

	var data = [];
	for (let i = 0; i < series.length; i++) {
		let each = series[i];
		let listItem = {};
		listItem.color = each.color;
		listItem.legendShape = each.legendShape;
		listItem.pointShape = each.pointShape;
		listItem.data = [];
		each.data.forEach(function(item, index) {
			let tmp = {};
			tmp.angle = angleList[index];

			tmp.proportion = item / maxData;
			tmp.position = convertCoordinateOrigin(radius * tmp.proportion * process * Math.cos(tmp.angle),
				radius * tmp.proportion *
				process * Math.sin(tmp.angle), center);
			listItem.data.push(tmp);
		});

		data.push(listItem);
	}

	return data;
}

function getPieDataPoints(series, radius) {
	var process = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 1;

	var count = 0;
	var _start_ = 0;
	for (let i = 0; i < series.length; i++) {
		let item = series[i];
		item.data = item.data === null ? 0 : item.data;
		count += item.data;
	}
	for (let i = 0; i < series.length; i++) {
		let item = series[i];
		item.data = item.data === null ? 0 : item.data;
		if (count === 0) {
			item._proportion_ = 1 / series.length * process;
		} else {
			item._proportion_ = item.data / count * process;
		}
		item._radius_ = radius;
	}
	for (let i = 0; i < series.length; i++) {
		let item = series[i];
		item._start_ = _start_;
		_start_ += 2 * item._proportion_ * Math.PI;
	}

	return series;
}

function getFunnelDataPoints(series, radius) {
	var process = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 1;
	series = series.sort(function(a, b) {
		return parseInt(b.data) - parseInt(a.data);
	});
	for (let i = 0; i < series.length; i++) {
		series[i].radius = series[i].data / series[0].data * radius * process;
		series[i]._proportion_ = series[i].data / series[0].data;
	}
	return series.reverse();
}

function getRoseDataPoints(series, type, minRadius, radius) {
	var process = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 1;
	var count = 0;
	var _start_ = 0;

	var dataArr = [];
	for (let i = 0; i < series.length; i++) {
		let item = series[i];
		item.data = item.data === null ? 0 : item.data;
		count += item.data;
		dataArr.push(item.data);
	}

	var minData = Math.min.apply(null, dataArr);
	var maxData = Math.max.apply(null, dataArr);
	var radiusLength = radius - minRadius;

	for (let i = 0; i < series.length; i++) {
		let item = series[i];
		item.data = item.data === null ? 0 : item.data;
		if (count === 0 || type == 'area') {
			item._proportion_ = item.data / count * process;
			item._rose_proportion_ = 1 / series.length * process;
		} else {
			item._proportion_ = item.data / count * process;
			item._rose_proportion_ = item.data / count * process;
		}
		item._radius_ = minRadius + radiusLength * ((item.data - minData) / (maxData - minData));
	}
	for (let i = 0; i < series.length; i++) {
		let item = series[i];
		item._start_ = _start_;
		_start_ += 2 * item._rose_proportion_ * Math.PI;
	}

	return series;
}

function getArcbarDataPoints(series, arcbarOption) {
	var process = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 1;
	if (process == 1) {
		process = 0.999999;
	}
	for (let i = 0; i < series.length; i++) {
		let item = series[i];
		item.data = item.data === null ? 0 : item.data;
		let totalAngle;
		if (arcbarOption.type == 'circle') {
			totalAngle = 2;
		} else {
			if (arcbarOption.endAngle < arcbarOption.startAngle) {
				totalAngle = 2 + arcbarOption.endAngle - arcbarOption.startAngle;
			} else {
				totalAngle = arcbarOption.startAngle - arcbarOption.endAngle;
			}
		}
		item._proportion_ = totalAngle * item.data * process + arcbarOption.startAngle;
		if (item._proportion_ >= 2) {
			item._proportion_ = item._proportion_ % 2;
		}
	}
	return series;
}

function getGaugeAxisPoints(categories, startAngle, endAngle) {
	let totalAngle = startAngle - endAngle + 1;
	let tempStartAngle = startAngle;
	for (let i = 0; i < categories.length; i++) {
		categories[i].value = categories[i].value === null ? 0 : categories[i].value;
		categories[i]._startAngle_ = tempStartAngle;
		categories[i]._endAngle_ = totalAngle * categories[i].value + startAngle;
		if (categories[i]._endAngle_ >= 2) {
			categories[i]._endAngle_ = categories[i]._endAngle_ % 2;
		}
		tempStartAngle = categories[i]._endAngle_;
	}
	return categories;
}

function getGaugeDataPoints(series, categories, gaugeOption) {
	let process = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 1;
	for (let i = 0; i < series.length; i++) {
		let item = series[i];
		item.data = item.data === null ? 0 : item.data;
		if (gaugeOption.pointer.color == 'auto') {
			for (let i = 0; i < categories.length; i++) {
				if (item.data <= categories[i].value) {
					item.color = categories[i].color;
					break;
				}
			}
		} else {
			item.color = gaugeOption.pointer.color;
		}
		let totalAngle = gaugeOption.startAngle - gaugeOption.endAngle + 1;
		item._endAngle_ = totalAngle * item.data + gaugeOption.startAngle;
		item._oldAngle_ = gaugeOption.oldAngle;
		if (gaugeOption.oldAngle < gaugeOption.endAngle) {
			item._oldAngle_ += 2;
		}
		if (item.data >= gaugeOption.oldData) {
			item._proportion_ = (item._endAngle_ - item._oldAngle_) * process + gaugeOption.oldAngle;
		} else {
			item._proportion_ = item._oldAngle_ - (item._oldAngle_ - item._endAngle_) * process;
		}
		if (item._proportion_ >= 2) {
			item._proportion_ = item._proportion_ % 2;
		}
	}
	return series;
}

function getPieTextMaxLength(series) {
	series = getPieDataPoints(series);
	let maxLength = 0;
	for (let i = 0; i < series.length; i++) {
		let item = series[i];
		let text = item.format ? item.format(+item._proportion_.toFixed(2)) : util.toFixed(item._proportion_ * 100) +
			'%';
		maxLength = Math.max(maxLength, measureText(text));
	}

	return maxLength;
}

function fixColumeData(points, eachSpacing, columnLen, index, config, opts) {
	return points.map(function(item) {
		if (item === null) {
			return null;
		}
		item.width = Math.ceil((eachSpacing - 2 * config.columePadding) / columnLen);

		if (opts.extra.column && opts.extra.column.width && +opts.extra.column.width > 0) {
			item.width = Math.min(item.width, +opts.extra.column.width);
		}
		if (item.width <= 0) {
			item.width = 1;
		}
		item.x += (index + 0.5 - columnLen / 2) * item.width;
		return item;
	});
}

function fixColumeMeterData(points, eachSpacing, columnLen, index, config, opts, border) {
	return points.map(function(item) {
		if (item === null) {
			return null;
		}
		item.width = Math.ceil((eachSpacing - 2 * config.columePadding) / 2);

		if (opts.extra.column && opts.extra.column.width && +opts.extra.column.width > 0) {
			item.width = Math.min(item.width, +opts.extra.column.width);
		}

		if (index > 0) {
			item.width -= 2 * border;
		}
		return item;
	});
}

function fixColumeStackData(points, eachSpacing, columnLen, index, config, opts, series) {

	return points.map(function(item, indexn) {

		if (item === null) {
			return null;
		}
		item.width = Math.ceil((eachSpacing - 2 * config.columePadding) / 2);

		if (opts.extra.column && opts.extra.column.width && +opts.extra.column.width > 0) {
			item.width = Math.min(item.width, +opts.extra.column.width);
		}
		return item;
	});
}

function getXAxisPoints(categories, opts, config) {
	var spacingValid = opts.width - opts.area[1] - opts.area[3];
	var dataCount = opts.enableScroll ? Math.min(opts.xAxis.itemCount, categories.length) : categories.length;
	if ((opts.type == 'line' || opts.type == 'area') && dataCount > 1 && opts.xAxis.boundaryGap == 'justify') {
		dataCount -= 1;
	}
	var eachSpacing = spacingValid / dataCount;

	var xAxisPoints = [];
	var startX = opts.area[3];
	var endX = opts.width - opts.area[1];
	categories.forEach(function(item, index) {
		xAxisPoints.push(startX + index * eachSpacing);
	});
	if (opts.xAxis.boundaryGap !== 'justify') {
		if (opts.enableScroll === true) {
			xAxisPoints.push(startX + categories.length * eachSpacing);
		} else {
			xAxisPoints.push(endX);
		}
	}
	return {
		xAxisPoints: xAxisPoints,
		startX: startX,
		endX: endX,
		eachSpacing: eachSpacing
	};
}

function getCandleDataPoints(data, minRange, maxRange, xAxisPoints, eachSpacing, opts, config) {
	var process = arguments.length > 7 && arguments[7] !== undefined ? arguments[7] : 1;
	var points = [];
	var validHeight = opts.height - opts.area[0] - opts.area[2];
	data.forEach(function(item, index) {
		if (item === null) {
			points.push(null);
		} else {
			var cPoints = [];
			item.forEach(function(items, indexs) {
				var point = {};
				point.x = xAxisPoints[index] + Math.round(eachSpacing / 2);
				var value = items.value || items;
				var height = validHeight * (value - minRange) / (maxRange - minRange);
				height *= process;
				point.y = opts.height - Math.round(height) - opts.area[2];
				cPoints.push(point);
			});
			points.push(cPoints);
		}
	});

	return points;
}

function getDataPoints(data, minRange, maxRange, xAxisPoints, eachSpacing, opts, config) {
	var process = arguments.length > 7 && arguments[7] !== undefined ? arguments[7] : 1;
	var boundaryGap = 'center';
	if (opts.type == 'line' || opts.type == 'area') {
		boundaryGap = opts.xAxis.boundaryGap;
	}
	var points = [];
	var validHeight = opts.height - opts.area[0] - opts.area[2];
	var validWidth = opts.width - opts.area[1] - opts.area[3];
	data.forEach(function(item, index) {
		if (item === null) {
			points.push(null);
		} else {
			var point = {};
			point.color = item.color;
			point.x = xAxisPoints[index];
			var value = item;
			if (typeof item === 'object' && item !== null) {
				if (item.constructor == Array) {
					let xranges, xminRange, xmaxRange;
					xranges = [].concat(opts.chartData.xAxisData.ranges);
					xminRange = xranges.shift();
					xmaxRange = xranges.pop();
					value = item[1];
					point.x = opts.area[3] + validWidth * (item[0] - xminRange) / (xmaxRange - xminRange);
				} else {
					value = item.value;
				}
			}
			if (boundaryGap == 'center') {
				point.x += Math.round(eachSpacing / 2);
			}
			var height = validHeight * (value - minRange) / (maxRange - minRange);
			height *= process;
			point.y = opts.height - Math.round(height) - opts.area[2];
			points.push(point);
		}
	});

	return points;
}

function getStackDataPoints(data, minRange, maxRange, xAxisPoints, eachSpacing, opts, config, seriesIndex,
stackSeries) {
	var process = arguments.length > 9 && arguments[9] !== undefined ? arguments[9] : 1;
	var points = [];
	var validHeight = opts.height - opts.area[0] - opts.area[2];

	data.forEach(function(item, index) {
		if (item === null) {
			points.push(null);
		} else {
			var point = {};
			point.color = item.color;
			point.x = xAxisPoints[index] + Math.round(eachSpacing / 2);

			if (seriesIndex > 0) {
				var value = 0;
				for (let i = 0; i <= seriesIndex; i++) {
					value += stackSeries[i].data[index];
				}
				var value0 = value - item;
				var height = validHeight * (value - minRange) / (maxRange - minRange);
				var height0 = validHeight * (value0 - minRange) / (maxRange - minRange);
			} else {
				var value = item;
				var height = validHeight * (value - minRange) / (maxRange - minRange);
				var height0 = 0;
			}
			var heightc = height0;
			height *= process;
			heightc *= process;
			point.y = opts.height - Math.round(height) - opts.area[2];
			point.y0 = opts.height - Math.round(heightc) - opts.area[2];
			points.push(point);
		}
	});

	return points;
}

function getYAxisTextList(series, opts, config, stack) {
	var index = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : -1;
	var data;
	if (stack == 'stack') {
		data = dataCombineStack(series, opts.categories.length);
	} else {
		data = dataCombine(series);
	}
	var sorted = [];
	// remove null from data
	data = data.filter(function(item) {
		//return item !== null;
		if (typeof item === 'object' && item !== null) {
			if (item.constructor == Array) {
				return item !== null;
			} else {
				return item.value !== null;
			}
		} else {
			return item !== null;
		}
	});
	data.map(function(item) {
		if (typeof item === 'object') {
			if (item.constructor == Array) {
				if (opts.type == 'candle') {
					item.map(function(subitem) {
						sorted.push(subitem);
					})
				} else {
					sorted.push(item[1]);
				}
			} else {
				sorted.push(item.value);
			}
		} else {
			sorted.push(item);
		}
	})

	var minData = 0;
	var maxData = 0;
	if (sorted.length > 0) {
		minData = Math.min.apply(this, sorted);
		maxData = Math.max.apply(this, sorted);
	}
	//为了兼容v1.9.0之前的项目
	if (index > -1) {
		if (typeof opts.yAxis.data[index].min === 'number') {
			minData = Math.min(opts.yAxis.data[index].min, minData);
		}
		if (typeof opts.yAxis.data[index].max === 'number') {
			maxData = Math.max(opts.yAxis.data[index].max, maxData);
		}
	} else {
		if (typeof opts.yAxis.min === 'number') {
			minData = Math.min(opts.yAxis.min, minData);
		}
		if (typeof opts.yAxis.max === 'number') {
			maxData = Math.max(opts.yAxis.max, maxData);
		}
	}


	if (minData === maxData) {
		var rangeSpan = maxData || 10;
		maxData += rangeSpan;
	}

	var dataRange = getDataRange(minData, maxData);
	var minRange = dataRange.minRange;
	var maxRange = dataRange.maxRange;

	var range = [];
	var eachRange = (maxRange - minRange) / opts.yAxis.splitNumber;

	for (var i = 0; i <= opts.yAxis.splitNumber; i++) {
		range.push(minRange + eachRange * i);
	}
	return range.reverse();
}

function calYAxisData(series, opts, config) {
	//堆叠图重算Y轴
	var columnstyle = assign({}, {
		type: ""
	}, opts.extra.column);
	//如果是多Y轴，重新计算
	var YLength = opts.yAxis.data.length;
	var newSeries = new Array(YLength);
	if (YLength > 0) {
		for (let i = 0; i < YLength; i++) {
			newSeries[i] = [];
			for (let j = 0; j < series.length; j++) {
				if (series[j].index == i) {
					newSeries[i].push(series[j]);
				}
			}
		}
		var rangesArr = new Array(YLength);
		var rangesFormatArr = new Array(YLength);
		var yAxisWidthArr = new Array(YLength);

		for (let i = 0; i < YLength; i++) {
			let yData = opts.yAxis.data[i];
			//如果总开关不显示，强制每个Y轴为不显示
			if (opts.yAxis.disabled == true) {
				yData.disabled = true;
			}
			rangesArr[i] = getYAxisTextList(newSeries[i], opts, config, columnstyle.type, i);
			let yAxisFontSizes = yData.fontSize || config.fontSize;
			yAxisWidthArr[i] = {
				position: yData.position ? yData.position : 'left',
				width: 0
			};
			rangesFormatArr[i] = rangesArr[i].map(function(items) {
				items = util.toFixed(items, 6);
				items = yData.format ? yData.format(Number(items)) : items;
				yAxisWidthArr[i].width = Math.max(yAxisWidthArr[i].width, measureText(items, yAxisFontSizes) +
					5);
				return items;
			});
			let calibration = yData.calibration ? 4 * opts.pixelRatio : 0;
			yAxisWidthArr[i].width += calibration + 3 * opts.pixelRatio;
			if (yData.disabled === true) {
				yAxisWidthArr[i].width = 0;
			}
		}

	} else {
		var rangesArr = new Array(1);
		var rangesFormatArr = new Array(1);
		var yAxisWidthArr = new Array(1);
		rangesArr[0] = getYAxisTextList(series, opts, config, columnstyle.type);
		yAxisWidthArr[0] = {
			position: 'left',
			width: 0
		};
		var yAxisFontSize = opts.yAxis.fontSize || config.fontSize;
		rangesFormatArr[0] = rangesArr[0].map(function(item) {
			item = util.toFixed(item, 6);
			item = opts.yAxis.format ? opts.yAxis.format(Number(item)) : item;
			yAxisWidthArr[0].width = Math.max(yAxisWidthArr[0].width, measureText(item, yAxisFontSize) + 5);
			return item;
		});
		yAxisWidthArr[0].width += 3 * opts.pixelRatio;
		if (opts.yAxis.disabled === true) {
			yAxisWidthArr[0] = {
				position: 'left',
				width: 0
			};
			opts.yAxis.data[0] = {
				disabled: true
			};
		} else {
			opts.yAxis.data[0] = {
				disabled: false,
				position: 'left',
				max: opts.yAxis.max,
				min: opts.yAxis.min,
				format: opts.yAxis.format
			};
		}

	}

	return {
		rangesFormat: rangesFormatArr,
		ranges: rangesArr,
		yAxisWidth: yAxisWidthArr
	};

}

function calTooltipYAxisData(point, series, opts, config, eachSpacing) {
	let ranges = [].concat(opts.chartData.yAxisData.ranges);
	let spacingValid = opts.height - opts.area[0] - opts.area[2];
	let minAxis = opts.area[0];
	let items = [];
	for (let i = 0; i < ranges.length; i++) {
		let maxVal = ranges[i].shift();
		let minVal = ranges[i].pop();
		let item = maxVal - (maxVal - minVal) * (point - minAxis) / spacingValid;
		item = opts.yAxis.data[i].format ? opts.yAxis.data[i].format(Number(item)) : item.toFixed(0);
		items.push(String(item))
	}
	return items;
}

function calMarkLineData(points, opts) {
	let minRange, maxRange;
	let spacingValid = opts.height - opts.area[0] - opts.area[2];
	for (let i = 0; i < points.length; i++) {
		points[i].yAxisIndex = points[i].yAxisIndex ? points[i].yAxisIndex : 0;
		let range = [].concat(opts.chartData.yAxisData.ranges[points[i].yAxisIndex]);
		minRange = range.pop();
		maxRange = range.shift();
		let height = spacingValid * (points[i].value - minRange) / (maxRange - minRange);
		points[i].y = opts.height - Math.round(height) - opts.area[2];
	}
	return points;
}

function contextRotate(context, opts) {
	if (opts.rotateLock !== true) {
		context.translate(opts.height, 0);
		context.rotate(90 * Math.PI / 180);
	} else if (opts._rotate_ !== true) {
		context.translate(opts.height, 0);
		context.rotate(90 * Math.PI / 180);
		opts._rotate_ = true;
	}
}

function drawPointShape(points, color, shape, context, opts) {
	context.beginPath();
	if (opts.dataPointShapeType == 'hollow') {
		context.setStrokeStyle(color);
		context.setFillStyle(opts.background);
		context.setLineWidth(2 * opts.pixelRatio);
	} else {
		context.setStrokeStyle("#ffffff");
		context.setFillStyle(color);
		context.setLineWidth(1 * opts.pixelRatio);
	}
	if (shape === 'diamond') {
		points.forEach(function(item, index) {
			if (item !== null) {
				context.moveTo(item.x, item.y - 4.5);
				context.lineTo(item.x - 4.5, item.y);
				context.lineTo(item.x, item.y + 4.5);
				context.lineTo(item.x + 4.5, item.y);
				context.lineTo(item.x, item.y - 4.5);
			}
		});
	} else if (shape === 'circle') {
		points.forEach(function(item, index) {
			if (item !== null) {
				context.moveTo(item.x + 2.5 * opts.pixelRatio, item.y);
				context.arc(item.x, item.y, 3 * opts.pixelRatio, 0, 2 * Math.PI, false);
			}
		});
	} else if (shape === 'rect') {
		points.forEach(function(item, index) {
			if (item !== null) {
				context.moveTo(item.x - 3.5, item.y - 3.5);
				context.rect(item.x - 3.5, item.y - 3.5, 7, 7);
			}
		});
	} else if (shape === 'triangle') {
		points.forEach(function(item, index) {
			if (item !== null) {
				context.moveTo(item.x, item.y - 4.5);
				context.lineTo(item.x - 4.5, item.y + 4.5);
				context.lineTo(item.x + 4.5, item.y + 4.5);
				context.lineTo(item.x, item.y - 4.5);
			}
		});
	}
	context.closePath();
	context.fill();
	context.stroke();
}

function drawRingTitle(opts, config, context, center) {
	var titlefontSize = opts.title.fontSize || config.titleFontSize;
	var subtitlefontSize = opts.subtitle.fontSize || config.subtitleFontSize;
	var title = opts.title.name || '';
	var subtitle = opts.subtitle.name || '';
	var titleFontColor = opts.title.color || config.titleColor;
	var subtitleFontColor = opts.subtitle.color || config.subtitleColor;
	var titleHeight = title ? titlefontSize : 0;
	var subtitleHeight = subtitle ? subtitlefontSize : 0;
	var margin = 5;

	if (subtitle) {
		var textWidth = measureText(subtitle, subtitlefontSize);
		var startX = center.x - textWidth / 2 + (opts.subtitle.offsetX || 0);
		var startY = center.y + subtitlefontSize / 2 + (opts.subtitle.offsetY || 0);
		if (title) {
			startY += (titleHeight + margin) / 2;
		}
		context.beginPath();
		context.setFontSize(subtitlefontSize);
		context.setFillStyle(subtitleFontColor);
		context.fillText(subtitle, startX, startY);
		context.closePath();
		context.stroke();
	}
	if (title) {
		var _textWidth = measureText(title, titlefontSize);
		var _startX = center.x - _textWidth / 2 + (opts.title.offsetX || 0);
		var _startY = center.y + titlefontSize / 2 + (opts.title.offsetY || 0);
		if (subtitle) {
			_startY -= (subtitleHeight + margin) / 2;
		}
		context.beginPath();
		context.setFontSize(titlefontSize);
		context.setFillStyle(titleFontColor);
		context.fillText(title, _startX, _startY);
		context.closePath();
		context.stroke();
	}
}

function drawPointText(points, series, config, context) {
	// 绘制数据文案
	var data = series.data;
	points.forEach(function(item, index) {
		if (item !== null) {
			//var formatVal = series.format ? series.format(data[index]) : data[index];
			context.beginPath();
			context.setFontSize(series.textSize || config.fontSize);
			context.setFillStyle(series.textColor || '#666666');
			var value = data[index]
			if (typeof data[index] === 'object' && data[index] !== null) {
				if (data[index].constructor == Array) {
					value = data[index][1];
				} else {
					value = data[index].value
				}
			}
			var formatVal = series.format ? series.format(value) : value;
			context.fillText(String(formatVal), item.x - measureText(formatVal, series.textSize || config
				.fontSize) / 2, item.y - 4);
			context.closePath();
			context.stroke();
		}
	});

}

function drawGaugeLabel(gaugeOption, radius, centerPosition, opts, config, context) {
	radius -= gaugeOption.width / 2 + config.gaugeLabelTextMargin;

	let totalAngle = gaugeOption.startAngle - gaugeOption.endAngle + 1;
	let splitAngle = totalAngle / gaugeOption.splitLine.splitNumber;
	let totalNumber = gaugeOption.endNumber - gaugeOption.startNumber;
	let splitNumber = totalNumber / gaugeOption.splitLine.splitNumber;
	let nowAngle = gaugeOption.startAngle;
	let nowNumber = gaugeOption.startNumber;
	for (let i = 0; i < gaugeOption.splitLine.splitNumber + 1; i++) {
		var pos = {
			x: radius * Math.cos(nowAngle * Math.PI),
			y: radius * Math.sin(nowAngle * Math.PI)
		};
		var labelText = gaugeOption.labelFormat ? gaugeOption.labelFormat(nowNumber) : nowNumber;
		pos.x += centerPosition.x - measureText(labelText) / 2;
		pos.y += centerPosition.y;
		var startX = pos.x;
		var startY = pos.y;
		context.beginPath();
		context.setFontSize(config.fontSize);
		context.setFillStyle(gaugeOption.labelColor || '#666666');
		context.fillText(labelText, startX, startY + config.fontSize / 2);
		context.closePath();
		context.stroke();

		nowAngle += splitAngle;
		if (nowAngle >= 2) {
			nowAngle = nowAngle % 2;
		}
		nowNumber += splitNumber;
	}

}

function drawRadarLabel(angleList, radius, centerPosition, opts, config, context) {
	var radarOption = opts.extra.radar || {};
	radius += config.radarLabelTextMargin;

	angleList.forEach(function(angle, index) {
		var pos = {
			x: radius * Math.cos(angle),
			y: radius * Math.sin(angle)
		};
		var posRelativeCanvas = convertCoordinateOrigin(pos.x, pos.y, centerPosition);
		var startX = posRelativeCanvas.x;
		var startY = posRelativeCanvas.y;
		if (util.approximatelyEqual(pos.x, 0)) {
			startX -= measureText(opts.categories[index] || '') / 2;
		} else if (pos.x < 0) {
			startX -= measureText(opts.categories[index] || '');
		}
		context.beginPath();
		context.setFontSize(config.fontSize);
		context.setFillStyle(radarOption.labelColor || '#666666');
		context.fillText(opts.categories[index] || '', startX, startY + config.fontSize / 2);
		context.closePath();
		context.stroke();
	});

}

function drawPieText(series, opts, config, context, radius, center) {
	var lineRadius = config.pieChartLinePadding;
	var textObjectCollection = [];
	var lastTextObject = null;

	var seriesConvert = series.map(function(item) {
		var text = item.format ? item.format(+item._proportion_.toFixed(2)) : util.toFixed(item._proportion_
			.toFixed(4) * 100) + '%';
		if (item._rose_proportion_) item._proportion_ = item._rose_proportion_;
		var arc = 2 * Math.PI - (item._start_ + 2 * Math.PI * item._proportion_ / 2);
		var color = item.color;
		var radius = item._radius_;
		return {
			arc: arc,
			text: text,
			color: color,
			radius: radius,
			textColor: item.textColor,
			textSize: item.textSize,
		};
	});
	for (let i = 0; i < seriesConvert.length; i++) {
		let item = seriesConvert[i];
		// line end
		let orginX1 = Math.cos(item.arc) * (item.radius + lineRadius);
		let orginY1 = Math.sin(item.arc) * (item.radius + lineRadius);

		// line start
		let orginX2 = Math.cos(item.arc) * item.radius;
		let orginY2 = Math.sin(item.arc) * item.radius;

		// text start
		let orginX3 = orginX1 >= 0 ? orginX1 + config.pieChartTextPadding : orginX1 - config.pieChartTextPadding;
		let orginY3 = orginY1;
		let textWidth = measureText(item.text, item.textSize || config.fontSize);
		let startY = orginY3;

		if (lastTextObject && util.isSameXCoordinateArea(lastTextObject.start, {
				x: orginX3
			})) {
			if (orginX3 > 0) {
				startY = Math.min(orginY3, lastTextObject.start.y);
			} else if (orginX1 < 0) {
				startY = Math.max(orginY3, lastTextObject.start.y);
			} else {
				if (orginY3 > 0) {
					startY = Math.max(orginY3, lastTextObject.start.y);
				} else {
					startY = Math.min(orginY3, lastTextObject.start.y);
				}
			}
		}
		if (orginX3 < 0) {
			orginX3 -= textWidth;
		}

		let textObject = {
			lineStart: {
				x: orginX2,
				y: orginY2
			},
			lineEnd: {
				x: orginX1,
				y: orginY1
			},
			start: {
				x: orginX3,
				y: startY
			},
			width: textWidth,
			height: config.fontSize,
			text: item.text,
			color: item.color,
			textColor: item.textColor,
			textSize: item.textSize
		};
		lastTextObject = avoidCollision(textObject, lastTextObject);
		textObjectCollection.push(lastTextObject);
	}

	for (let i = 0; i < textObjectCollection.length; i++) {
		let item = textObjectCollection[i];
		let lineStartPoistion = convertCoordinateOrigin(item.lineStart.x, item.lineStart.y, center);
		let lineEndPoistion = convertCoordinateOrigin(item.lineEnd.x, item.lineEnd.y, center);
		let textPosition = convertCoordinateOrigin(item.start.x, item.start.y, center);
		context.setLineWidth(1 * opts.pixelRatio);
		context.setFontSize(config.fontSize);
		context.beginPath();
		context.setStrokeStyle(item.color);
		context.setFillStyle(item.color);
		context.moveTo(lineStartPoistion.x, lineStartPoistion.y);
		let curveStartX = item.start.x < 0 ? textPosition.x + item.width : textPosition.x;
		let textStartX = item.start.x < 0 ? textPosition.x - 5 : textPosition.x + 5;
		context.quadraticCurveTo(lineEndPoistion.x, lineEndPoistion.y, curveStartX, textPosition.y);
		context.moveTo(lineStartPoistion.x, lineStartPoistion.y);
		context.stroke();
		context.closePath();
		context.beginPath();
		context.moveTo(textPosition.x + item.width, textPosition.y);
		context.arc(curveStartX, textPosition.y, 2, 0, 2 * Math.PI);
		context.closePath();
		context.fill();
		context.beginPath();
		context.setFontSize(item.textSize || config.fontSize);
		context.setFillStyle(item.textColor || '#666666');
		context.fillText(item.text, textStartX, textPosition.y + 3);
		context.closePath();
		context.stroke();
		context.closePath();
	}
}

function drawToolTipSplitLine(offsetX, opts, config, context) {
	var toolTipOption = opts.extra.tooltip || {};
	toolTipOption.gridType = toolTipOption.gridType == undefined ? 'solid' : toolTipOption.gridType;
	toolTipOption.dashLength = toolTipOption.dashLength == undefined ? 4 : toolTipOption.dashLength;
	var startY = opts.area[0];
	var endY = opts.height - opts.area[2];

	if (toolTipOption.gridType == 'dash') {
		context.setLineDash([toolTipOption.dashLength, toolTipOption.dashLength]);
	}
	context.setStrokeStyle(toolTipOption.gridColor || '#cccccc');
	context.setLineWidth(1 * opts.pixelRatio);
	context.beginPath();
	context.moveTo(offsetX, startY);
	context.lineTo(offsetX, endY);
	context.stroke();
	context.setLineDash([]);

	if (toolTipOption.xAxisLabel) {
		let labelText = opts.categories[opts.tooltip.index];
		context.setFontSize(config.fontSize);
		let textWidth = measureText(labelText, config.fontSize);

		let textX = offsetX - 0.5 * textWidth;
		let textY = endY;
		context.beginPath();
		context.setFillStyle(hexToRgb(toolTipOption.labelBgColor || config.toolTipBackground, toolTipOption
			.labelBgOpacity || config.toolTipOpacity));
		context.setStrokeStyle(toolTipOption.labelBgColor || config.toolTipBackground);
		context.setLineWidth(1 * opts.pixelRatio);
		context.rect(textX - config.toolTipPadding, textY, textWidth + 2 * config.toolTipPadding, config.fontSize + 2 *
			config.toolTipPadding);
		context.closePath();
		context.stroke();
		context.fill();

		context.beginPath();
		context.setFontSize(config.fontSize);
		context.setFillStyle(toolTipOption.labelFontColor || config.fontColor);
		context.fillText(String(labelText), textX, textY + config.toolTipPadding + config.fontSize);
		context.closePath();
		context.stroke();
	}
}

function drawMarkLine(opts, config, context) {
	let markLineOption = assign({}, {
		type: 'solid',
		dashLength: 4,
		data: []
	}, opts.extra.markLine);
	let startX = opts.area[3];
	let endX = opts.width - opts.area[1];
	let points = calMarkLineData(markLineOption.data, opts);

	for (let i = 0; i < points.length; i++) {
		let item = assign({}, {
			lineColor: '#DE4A42',
			showLabel: false,
			labelFontColor: '#666666',
			labelBgColor: '#DFE8FF',
			labelBgOpacity: 0.8,
			yAxisIndex: 0
		}, points[i]);

		if (markLineOption.type == 'dash') {
			context.setLineDash([markLineOption.dashLength, markLineOption.dashLength]);
		}
		context.setStrokeStyle(item.lineColor);
		context.setLineWidth(1 * opts.pixelRatio);
		context.beginPath();
		context.moveTo(startX, item.y);
		context.lineTo(endX, item.y);
		context.stroke();
		context.setLineDash([]);
		if (item.showLabel) {
			let labelText = opts.yAxis.format ? opts.yAxis.format(Number(item.value)) : item.value;
			context.setFontSize(config.fontSize);
			let textWidth = measureText(labelText, config.fontSize);
			let bgStartX = opts.padding[3] + config.yAxisTitleWidth - config.toolTipPadding;
			let bgEndX = Math.max(opts.area[3], textWidth + config.toolTipPadding * 2);
			let bgWidth = bgEndX - bgStartX;

			let textX = bgStartX + (bgWidth - textWidth) / 2;
			let textY = item.y;
			context.setFillStyle(hexToRgb(item.labelBgColor, item.labelBgOpacity));
			context.setStrokeStyle(item.labelBgColor);
			context.setLineWidth(1 * opts.pixelRatio);
			context.beginPath();
			context.rect(bgStartX, textY - 0.5 * config.fontSize - config.toolTipPadding, bgWidth, config.fontSize + 2 *
				config.toolTipPadding);
			context.closePath();
			context.stroke();
			context.fill();

			context.beginPath();
			context.setFontSize(config.fontSize);
			context.setFillStyle(item.labelFontColor);
			context.fillText(String(labelText), textX, textY + 0.5 * config.fontSize);
			context.stroke();
		}
	}
}

function drawToolTipHorizentalLine(opts, config, context, eachSpacing, xAxisPoints) {
	var toolTipOption = assign({}, {
		gridType: 'solid',
		dashLength: 4
	}, opts.extra.tooltip);

	var startX = opts.area[3];
	var endX = opts.width - opts.area[1];

	if (toolTipOption.gridType == 'dash') {
		context.setLineDash([toolTipOption.dashLength, toolTipOption.dashLength]);
	}
	context.setStrokeStyle(toolTipOption.gridColor || '#cccccc');
	context.setLineWidth(1 * opts.pixelRatio);
	context.beginPath();
	context.moveTo(startX, opts.tooltip.offset.y);
	context.lineTo(endX, opts.tooltip.offset.y);
	context.stroke();
	context.setLineDash([]);

	if (toolTipOption.yAxisLabel) {
		let labelText = calTooltipYAxisData(opts.tooltip.offset.y, opts.series, opts, config, eachSpacing);
		let widthArr = opts.chartData.yAxisData.yAxisWidth;
		let tStartLeft = opts.area[3];
		let tStartRight = opts.width - opts.area[1];
		for (let i = 0; i < labelText.length; i++) {
			context.setFontSize(config.fontSize);
			let textWidth = measureText(labelText[i], config.fontSize);
			let bgStartX, bgEndX, bgWidth;
			if (widthArr[i].position == 'left') {
				bgStartX = tStartLeft - widthArr[i].width;
				bgEndX = Math.max(bgStartX, bgStartX + textWidth + config.toolTipPadding * 2);
			} else {
				bgStartX = tStartRight;
				bgEndX = Math.max(bgStartX + widthArr[i].width, bgStartX + textWidth + config.toolTipPadding * 2);
			}
			bgWidth = bgEndX - bgStartX;

			let textX = bgStartX + (bgWidth - textWidth) / 2;
			let textY = opts.tooltip.offset.y;
			context.beginPath();
			context.setFillStyle(hexToRgb(toolTipOption.labelBgColor || config.toolTipBackground, toolTipOption
				.labelBgOpacity || config.toolTipOpacity));
			context.setStrokeStyle(toolTipOption.labelBgColor || config.toolTipBackground);
			context.setLineWidth(1 * opts.pixelRatio);
			context.rect(bgStartX, textY - 0.5 * config.fontSize - config.toolTipPadding, bgWidth, config.fontSize + 2 *
				config.toolTipPadding);
			context.closePath();
			context.stroke();
			context.fill();

			context.beginPath();
			context.setFontSize(config.fontSize);
			context.setFillStyle(toolTipOption.labelFontColor || config.fontColor);
			context.fillText(labelText[i], textX, textY + 0.5 * config.fontSize);
			context.closePath();
			context.stroke();
			if (widthArr[i].position == 'left') {
				tStartLeft -= (widthArr[i].width + opts.yAxis.padding);
			} else {
				tStartRight += widthArr[i].width + opts.yAxis.padding;
			}
		}
	}
}

function drawToolTipSplitArea(offsetX, opts, config, context, eachSpacing) {
	var toolTipOption = assign({}, {
		activeBgColor: '#000000',
		activeBgOpacity: 0.08
	}, opts.extra.tooltip);
	var startY = opts.area[0];
	var endY = opts.height - opts.area[2];
	context.beginPath();
	context.setFillStyle(hexToRgb(toolTipOption.activeBgColor, toolTipOption.activeBgOpacity));
	context.rect(offsetX - eachSpacing / 2, startY, eachSpacing, endY - startY);
	context.closePath();
	context.fill();
}

function drawToolTip(textList, offset, opts, config, context, eachSpacing, xAxisPoints) {
	var toolTipOption = assign({}, {
		showBox: true,
		bgColor: '#000000',
		bgOpacity: 0.7,
		fontColor: '#FFFFFF'
	}, opts.extra.tooltip);
	var legendWidth = 4 * opts.pixelRatio;
	var legendMarginRight = 5 * opts.pixelRatio;
	var arrowWidth = 8 * opts.pixelRatio;
	var isOverRightBorder = false;
	if (opts.type == 'line' || opts.type == 'area' || opts.type == 'candle' || opts.type == 'mix') {
		drawToolTipSplitLine(opts.tooltip.offset.x, opts, config, context);
	}

	offset = assign({
		x: 0,
		y: 0
	}, offset);
	offset.y -= 8 * opts.pixelRatio;
	var textWidth = textList.map(function(item) {
		return measureText(item.text, config.fontSize);
	});
	var toolTipWidth = legendWidth + legendMarginRight + 4 * config.toolTipPadding + Math.max.apply(null, textWidth);
	var toolTipHeight = 2 * config.toolTipPadding + textList.length * config.toolTipLineHeight;

	if (toolTipOption.showBox == false) {
		return
	}
	// if beyond the right border
	if (offset.x - Math.abs(opts._scrollDistance_) + arrowWidth + toolTipWidth > opts.width) {
		isOverRightBorder = true;
	}
	if (toolTipHeight + offset.y > opts.height) {
		offset.y = opts.height - toolTipHeight;
	}
	// draw background rect
	context.beginPath();
	context.setFillStyle(hexToRgb(toolTipOption.bgColor || config.toolTipBackground, toolTipOption.bgOpacity || config
		.toolTipOpacity));
	if (isOverRightBorder) {
		context.moveTo(offset.x, offset.y + 10 * opts.pixelRatio);
		context.lineTo(offset.x - arrowWidth, offset.y + 10 * opts.pixelRatio - 5 * opts.pixelRatio);
		context.lineTo(offset.x - arrowWidth, offset.y);
		context.lineTo(offset.x - arrowWidth - Math.round(toolTipWidth), offset.y);
		context.lineTo(offset.x - arrowWidth - Math.round(toolTipWidth), offset.y + toolTipHeight);
		context.lineTo(offset.x - arrowWidth, offset.y + toolTipHeight);
		context.lineTo(offset.x - arrowWidth, offset.y + 10 * opts.pixelRatio + 5 * opts.pixelRatio);
		context.lineTo(offset.x, offset.y + 10 * opts.pixelRatio);
	} else {
		context.moveTo(offset.x, offset.y + 10 * opts.pixelRatio);
		context.lineTo(offset.x + arrowWidth, offset.y + 10 * opts.pixelRatio - 5 * opts.pixelRatio);
		context.lineTo(offset.x + arrowWidth, offset.y);
		context.lineTo(offset.x + arrowWidth + Math.round(toolTipWidth), offset.y);
		context.lineTo(offset.x + arrowWidth + Math.round(toolTipWidth), offset.y + toolTipHeight);
		context.lineTo(offset.x + arrowWidth, offset.y + toolTipHeight);
		context.lineTo(offset.x + arrowWidth, offset.y + 10 * opts.pixelRatio + 5 * opts.pixelRatio);
		context.lineTo(offset.x, offset.y + 10 * opts.pixelRatio);
	}

	context.closePath();
	context.fill();

	// draw legend
	textList.forEach(function(item, index) {
		if (item.color !== null) {
			context.beginPath();
			context.setFillStyle(item.color);
			var startX = offset.x + arrowWidth + 2 * config.toolTipPadding;
			var startY = offset.y + (config.toolTipLineHeight - config.fontSize) / 2 + config
				.toolTipLineHeight * index +
				config.toolTipPadding + 1;
			if (isOverRightBorder) {
				startX = offset.x - toolTipWidth - arrowWidth + 2 * config.toolTipPadding;
			}
			context.fillRect(startX, startY, legendWidth, config.fontSize);
			context.closePath();
		}
	});

	// draw text list

	textList.forEach(function(item, index) {
		var startX = offset.x + arrowWidth + 2 * config.toolTipPadding + legendWidth + legendMarginRight;
		if (isOverRightBorder) {
			startX = offset.x - toolTipWidth - arrowWidth + 2 * config.toolTipPadding + +legendWidth +
				legendMarginRight;
		}
		var startY = offset.y + (config.toolTipLineHeight - config.fontSize) / 2 + config.toolTipLineHeight *
			index +
			config.toolTipPadding;
		context.beginPath();
		context.setFontSize(config.fontSize);
		context.setFillStyle(toolTipOption.fontColor);
		context.fillText(item.text, startX, startY + config.fontSize);
		context.closePath();
		context.stroke();
	});
}

function drawYAxisTitle(title, opts, config, context) {
	var startX = config.xAxisHeight + (opts.height - config.xAxisHeight - measureText(title)) / 2;
	context.save();
	context.beginPath();
	context.setFontSize(config.fontSize);
	context.setFillStyle(opts.yAxis.titleFontColor || '#333333');
	context.translate(0, opts.height);
	context.rotate(-90 * Math.PI / 180);
	context.fillText(title, startX, opts.padding[3] + 0.5 * config.fontSize);
	context.closePath();
	context.stroke();
	context.restore();
}

function drawColumnDataPoints(series, opts, config, context) {
	let process = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 1;
	let xAxisData = opts.chartData.xAxisData,
		xAxisPoints = xAxisData.xAxisPoints,
		eachSpacing = xAxisData.eachSpacing;
	let columnOption = assign({}, {
		type: 'group',
		width: eachSpacing / 2,
		meter: {
			border: 4,
			fillColor: '#FFFFFF'
		}
	}, opts.extra.column);

	let calPoints = [];
	context.save();

	let leftNum = -2;
	let rightNum = xAxisPoints.length + 2;

	if (opts._scrollDistance_ && opts._scrollDistance_ !== 0 && opts.enableScroll === true) {
		context.translate(opts._scrollDistance_, 0);
		leftNum = Math.floor(-opts._scrollDistance_ / eachSpacing) - 2;
		rightNum = leftNum + opts.xAxis.itemCount + 4;
	}
	if (opts.tooltip && opts.tooltip.textList && opts.tooltip.textList.length && process === 1) {
		drawToolTipSplitArea(opts.tooltip.offset.x, opts, config, context, eachSpacing);
	}

	series.forEach(function(eachSeries, seriesIndex) {
		let ranges, minRange, maxRange;
		ranges = [].concat(opts.chartData.yAxisData.ranges[eachSeries.index]);
		minRange = ranges.pop();
		maxRange = ranges.shift();

		var data = eachSeries.data;
		switch (columnOption.type) {
			case 'group':
				var points = getDataPoints(data, minRange, maxRange, xAxisPoints, eachSpacing, opts, config,
					process);
				var tooltipPoints = getStackDataPoints(data, minRange, maxRange, xAxisPoints, eachSpacing, opts,
					config, seriesIndex, series, process);
				calPoints.push(tooltipPoints);
				points = fixColumeData(points, eachSpacing, series.length, seriesIndex, config, opts);
				for (let i = 0; i < points.length; i++) {
					let item = points[i];
					if (item !== null && i > leftNum && i < rightNum) {
						context.beginPath();
						context.setStrokeStyle(item.color || eachSeries.color);
						context.setLineWidth(1)
						context.setFillStyle(item.color || eachSeries.color);
						var startX = item.x - item.width / 2;
						var height = opts.height - item.y - opts.area[2];
						context.moveTo(startX, item.y);
						context.lineTo(startX + item.width - 2, item.y);
						context.lineTo(startX + item.width - 2, opts.height - opts.area[2]);
						context.lineTo(startX, opts.height - opts.area[2]);
						context.lineTo(startX, item.y);
						context.closePath();
						context.stroke();
						context.fill();
					}
				};
				break;
			case 'stack':
				// 绘制堆叠数据图
				var points = getStackDataPoints(data, minRange, maxRange, xAxisPoints, eachSpacing, opts,
					config, seriesIndex, series, process);
				calPoints.push(points);
				points = fixColumeStackData(points, eachSpacing, series.length, seriesIndex, config, opts,
					series);

				for (let i = 0; i < points.length; i++) {
					let item = points[i];
					if (item !== null && i > leftNum && i < rightNum) {
						context.beginPath();
						context.setFillStyle(item.color || eachSeries.color);
						var startX = item.x - item.width / 2 + 1;
						var height = opts.height - item.y - opts.area[2];
						var height0 = opts.height - item.y0 - opts.area[2];
						if (seriesIndex > 0) {
							height -= height0;
						}
						context.moveTo(startX, item.y);
						context.fillRect(startX, item.y, item.width - 2, height);
						context.closePath();
						context.fill();
					}
				};
				break;
			case 'meter':
				// 绘制温度计数据图
				var points = getDataPoints(data, minRange, maxRange, xAxisPoints, eachSpacing, opts, config,
					process);
				calPoints.push(points);
				points = fixColumeMeterData(points, eachSpacing, series.length, seriesIndex, config, opts,
					columnOption.meter.border);
				if (seriesIndex == 0) {
					for (let i = 0; i < points.length; i++) {
						let item = points[i];
						if (item !== null && i > leftNum && i < rightNum) {
							//画背景颜色
							context.beginPath();
							context.setFillStyle(columnOption.meter.fillColor);
							var startX = item.x - item.width / 2;
							var height = opts.height - item.y - opts.area[2];
							context.moveTo(startX, item.y);
							context.fillRect(startX, item.y, item.width, height);
							context.closePath();
							context.fill();
							//画边框线
							if (columnOption.meter.border > 0) {
								context.beginPath();
								context.setStrokeStyle(eachSeries.color);
								context.setLineWidth(columnOption.meter.border * opts.pixelRatio);
								context.moveTo(startX + columnOption.meter.border * 0.5, item.y + height);
								context.lineTo(startX + columnOption.meter.border * 0.5, item.y + columnOption
									.meter.border * 0.5);
								context.lineTo(startX + item.width - columnOption.meter.border * 0.5, item.y +
									columnOption.meter.border * 0.5);
								context.lineTo(startX + item.width - columnOption.meter.border * 0.5, item.y +
									height);
								context.stroke();
							}
						}
					};
				} else {
					for (let i = 0; i < points.length; i++) {
						let item = points[i];
						if (item !== null && i > leftNum && i < rightNum) {
							context.beginPath();
							context.setFillStyle(item.color || eachSeries.color);
							var startX = item.x - item.width / 2;
							var height = opts.height - item.y - opts.area[2];
							context.moveTo(startX, item.y);
							context.fillRect(startX, item.y, item.width, height);
							context.closePath();
							context.fill();
						}
					};
				}
				break;
		}
	});

	if (opts.dataLabel !== false && process === 1) {
		series.forEach(function(eachSeries, seriesIndex) {
			let ranges, minRange, maxRange;
			ranges = [].concat(opts.chartData.yAxisData.ranges[eachSeries.index]);
			minRange = ranges.pop();
			maxRange = ranges.shift();
			var data = eachSeries.data;
			switch (columnOption.type) {
				case 'group':
					var points = getDataPoints(data, minRange, maxRange, xAxisPoints, eachSpacing, opts, config,
						process);
					points = fixColumeData(points, eachSpacing, series.length, seriesIndex, config, opts);
					drawPointText(points, eachSeries, config, context);
					break;
				case 'stack':
					var points = getStackDataPoints(data, minRange, maxRange, xAxisPoints, eachSpacing, opts,
						config, seriesIndex, series, process);
					drawPointText(points, eachSeries, config, context);
					break;
				case 'meter':
					var points = getDataPoints(data, minRange, maxRange, xAxisPoints, eachSpacing, opts, config,
						process);
					drawPointText(points, eachSeries, config, context);
					break;
			}
		});
	}

	context.restore();

	return {
		xAxisPoints: xAxisPoints,
		calPoints: calPoints,
		eachSpacing: eachSpacing
	};
}

function drawCandleDataPoints(series, seriesMA, opts, config, context) {
	var process = arguments.length > 5 && arguments[5] !== undefined ? arguments[5] : 1;
	var candleOption = assign({}, {
		color: {},
		average: {}
	}, opts.extra.candle);
	candleOption.color = assign({}, {
		upLine: '#f04864',
		upFill: '#f04864',
		downLine: '#2fc25b',
		downFill: '#2fc25b'
	}, candleOption.color);
	candleOption.average = assign({}, {
		show: false,
		name: [],
		day: [],
		color: config.colors
	}, candleOption.average);
	opts.extra.candle = candleOption;

	let xAxisData = opts.chartData.xAxisData,
		xAxisPoints = xAxisData.xAxisPoints,
		eachSpacing = xAxisData.eachSpacing;

	let calPoints = [];

	context.save();

	let leftNum = -2;
	let rightNum = xAxisPoints.length + 2;
	let leftSpace = 0;
	let rightSpace = opts.width + eachSpacing;

	if (opts._scrollDistance_ && opts._scrollDistance_ !== 0 && opts.enableScroll === true) {
		context.translate(opts._scrollDistance_, 0);
		leftNum = Math.floor(-opts._scrollDistance_ / eachSpacing) - 2;
		rightNum = leftNum + opts.xAxis.itemCount + 4;
		leftSpace = -opts._scrollDistance_ - eachSpacing + opts.area[3];
		rightSpace = leftSpace + (opts.xAxis.itemCount + 4) * eachSpacing;
	}

	//画均线
	if (candleOption.average.show) {
		seriesMA.forEach(function(eachSeries, seriesIndex) {
			let ranges, minRange, maxRange;
			ranges = [].concat(opts.chartData.yAxisData.ranges[eachSeries.index]);
			minRange = ranges.pop();
			maxRange = ranges.shift();

			var data = eachSeries.data;
			var points = getDataPoints(data, minRange, maxRange, xAxisPoints, eachSpacing, opts, config,
				process);
			var splitPointList = splitPoints(points);

			for (let i = 0; i < splitPointList.length; i++) {
				let points = splitPointList[i];
				context.beginPath();
				context.setStrokeStyle(eachSeries.color);
				context.setLineWidth(1);
				if (points.length === 1) {
					context.moveTo(points[0].x, points[0].y);
					context.arc(points[0].x, points[0].y, 1, 0, 2 * Math.PI);
				} else {
					context.moveTo(points[0].x, points[0].y);
					let startPoint = 0;
					for (let j = 0; j < points.length; j++) {
						let item = points[j];
						if (startPoint == 0 && item.x > leftSpace) {
							context.moveTo(item.x, item.y);
							startPoint = 1;
						}
						if (j > 0 && item.x > leftSpace && item.x < rightSpace) {
							var ctrlPoint = createCurveControlPoints(points, j - 1);
							context.bezierCurveTo(ctrlPoint.ctrA.x, ctrlPoint.ctrA.y, ctrlPoint.ctrB.x,
								ctrlPoint.ctrB.y, item.x, item.y);
						}
					}
					context.moveTo(points[0].x, points[0].y);
				}
				context.closePath();
				context.stroke();
			}
		});
	}
	//画K线
	series.forEach(function(eachSeries, seriesIndex) {
		let ranges, minRange, maxRange;
		ranges = [].concat(opts.chartData.yAxisData.ranges[eachSeries.index]);
		minRange = ranges.pop();
		maxRange = ranges.shift();
		var data = eachSeries.data;
		var points = getCandleDataPoints(data, minRange, maxRange, xAxisPoints, eachSpacing, opts, config,
			process);
		calPoints.push(points);
		var splitPointList = splitPoints(points);

		for (let i = 0; i < splitPointList[0].length; i++) {
			if (i > leftNum && i < rightNum) {
				let item = splitPointList[0][i];
				context.beginPath();
				//如果上涨
				if (data[i][1] - data[i][0] > 0) {
					context.setStrokeStyle(candleOption.color.upLine);
					context.setFillStyle(candleOption.color.upFill);
					context.setLineWidth(1 * opts.pixelRatio);
					context.moveTo(item[3].x, item[3].y); //顶点
					context.lineTo(item[1].x, item[1].y); //收盘中间点
					context.lineTo(item[1].x - eachSpacing / 4, item[1].y); //收盘左侧点
					context.lineTo(item[0].x - eachSpacing / 4, item[0].y); //开盘左侧点
					context.lineTo(item[0].x, item[0].y); //开盘中间点
					context.lineTo(item[2].x, item[2].y); //底点
					context.lineTo(item[0].x, item[0].y); //开盘中间点
					context.lineTo(item[0].x + eachSpacing / 4, item[0].y); //开盘右侧点
					context.lineTo(item[1].x + eachSpacing / 4, item[1].y); //收盘右侧点
					context.lineTo(item[1].x, item[1].y); //收盘中间点
					context.moveTo(item[3].x, item[3].y); //顶点
				} else {
					context.setStrokeStyle(candleOption.color.downLine);
					context.setFillStyle(candleOption.color.downFill);
					context.setLineWidth(1 * opts.pixelRatio);
					context.moveTo(item[3].x, item[3].y); //顶点
					context.lineTo(item[0].x, item[0].y); //开盘中间点
					context.lineTo(item[0].x - eachSpacing / 4, item[0].y); //开盘左侧点
					context.lineTo(item[1].x - eachSpacing / 4, item[1].y); //收盘左侧点
					context.lineTo(item[1].x, item[1].y); //收盘中间点
					context.lineTo(item[2].x, item[2].y); //底点
					context.lineTo(item[1].x, item[1].y); //收盘中间点
					context.lineTo(item[1].x + eachSpacing / 4, item[1].y); //收盘右侧点
					context.lineTo(item[0].x + eachSpacing / 4, item[0].y); //开盘右侧点
					context.lineTo(item[0].x, item[0].y); //开盘中间点
					context.moveTo(item[3].x, item[3].y); //顶点
				}
				context.closePath();
				context.fill();
				context.stroke();
			}
		}
	});

	context.restore();

	return {
		xAxisPoints: xAxisPoints,
		calPoints: calPoints,
		eachSpacing: eachSpacing
	};
}

function drawAreaDataPoints(series, opts, config, context) {
	var process = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 1;
	var areaOption = assign({}, {
		type: 'straight',
		opacity: 0.2,
		addLine: false,
		width: 2,
		gradient: false
	}, opts.extra.area);

	let xAxisData = opts.chartData.xAxisData,
		xAxisPoints = xAxisData.xAxisPoints,
		eachSpacing = xAxisData.eachSpacing;

	let endY = opts.height - opts.area[2];
	let calPoints = [];

	context.save();
	let leftSpace = 0;
	let rightSpace = opts.width + eachSpacing;
	if (opts._scrollDistance_ && opts._scrollDistance_ !== 0 && opts.enableScroll === true) {
		context.translate(opts._scrollDistance_, 0);
		leftSpace = -opts._scrollDistance_ - eachSpacing + opts.area[3];
		rightSpace = leftSpace + (opts.xAxis.itemCount + 4) * eachSpacing;
	}

	series.forEach(function(eachSeries, seriesIndex) {
		let ranges, minRange, maxRange;
		ranges = [].concat(opts.chartData.yAxisData.ranges[eachSeries.index]);
		minRange = ranges.pop();
		maxRange = ranges.shift();
		let data = eachSeries.data;
		let points = getDataPoints(data, minRange, maxRange, xAxisPoints, eachSpacing, opts, config, process);
		calPoints.push(points);

		let splitPointList = splitPoints(points);
		for (let i = 0; i < splitPointList.length; i++) {
			let points = splitPointList[i];
			// 绘制区域数
			context.beginPath();
			context.setStrokeStyle(hexToRgb(eachSeries.color, areaOption.opacity));
			if (areaOption.gradient) {
				let gradient = context.createLinearGradient(0, opts.area[0], 0, opts.height - opts.area[2]);
				gradient.addColorStop('0', hexToRgb(eachSeries.color, areaOption.opacity));
				gradient.addColorStop('1.0', hexToRgb("#FFFFFF", 0.1));
				context.setFillStyle(gradient);
			} else {
				context.setFillStyle(hexToRgb(eachSeries.color, areaOption.opacity));
			}
			context.setLineWidth(areaOption.width * opts.pixelRatio);
			if (points.length > 1) {
				let firstPoint = points[0];
				let lastPoint = points[points.length - 1];
				context.moveTo(firstPoint.x, firstPoint.y);
				let startPoint = 0;
				if (areaOption.type === 'curve') {
					for (let j = 0; j < points.length; j++) {
						let item = points[j];
						if (startPoint == 0 && item.x > leftSpace) {
							context.moveTo(item.x, item.y);
							startPoint = 1;
						}
						if (j > 0 && item.x > leftSpace && item.x < rightSpace) {
							let ctrlPoint = createCurveControlPoints(points, j - 1);
							context.bezierCurveTo(ctrlPoint.ctrA.x, ctrlPoint.ctrA.y, ctrlPoint.ctrB.x,
								ctrlPoint.ctrB.y, item.x, item.y);
						}
					};
				} else {
					for (let j = 0; j < points.length; j++) {
						let item = points[j];
						if (startPoint == 0 && item.x > leftSpace) {
							context.moveTo(item.x, item.y);
							startPoint = 1;
						}
						if (j > 0 && item.x > leftSpace && item.x < rightSpace) {
							context.lineTo(item.x, item.y);
						}
					};
				}

				context.lineTo(lastPoint.x, endY);
				context.lineTo(firstPoint.x, endY);
				context.lineTo(firstPoint.x, firstPoint.y);
			} else {
				let item = points[0];
				context.moveTo(item.x - eachSpacing / 2, item.y);
				context.lineTo(item.x + eachSpacing / 2, item.y);
				context.lineTo(item.x + eachSpacing / 2, endY);
				context.lineTo(item.x - eachSpacing / 2, endY);
				context.moveTo(item.x - eachSpacing / 2, item.y);
			}
			context.closePath();
			context.fill();

			//画连线
			if (areaOption.addLine) {
				if (eachSeries.lineType == 'dash') {
					let dashLength = eachSeries.dashLength ? eachSeries.dashLength : 8;
					dashLength *= opts.pixelRatio;
					context.setLineDash([dashLength, dashLength]);
				}
				context.beginPath();
				context.setStrokeStyle(eachSeries.color);
				context.setLineWidth(areaOption.width * opts.pixelRatio);
				if (points.length === 1) {
					context.moveTo(points[0].x, points[0].y);
					context.arc(points[0].x, points[0].y, 1, 0, 2 * Math.PI);
				} else {
					context.moveTo(points[0].x, points[0].y);
					let startPoint = 0;
					if (areaOption.type === 'curve') {
						for (let j = 0; j < points.length; j++) {
							let item = points[j];
							if (startPoint == 0 && item.x > leftSpace) {
								context.moveTo(item.x, item.y);
								startPoint = 1;
							}
							if (j > 0 && item.x > leftSpace && item.x < rightSpace) {
								let ctrlPoint = createCurveControlPoints(points, j - 1);
								context.bezierCurveTo(ctrlPoint.ctrA.x, ctrlPoint.ctrA.y, ctrlPoint.ctrB.x,
									ctrlPoint.ctrB.y, item.x, item.y);
							}
						};
					} else {
						for (let j = 0; j < points.length; j++) {
							let item = points[j];
							if (startPoint == 0 && item.x > leftSpace) {
								context.moveTo(item.x, item.y);
								startPoint = 1;
							}
							if (j > 0 && item.x > leftSpace && item.x < rightSpace) {
								context.lineTo(item.x, item.y);
							}
						};
					}
					context.moveTo(points[0].x, points[0].y);
				}
				context.stroke();
				context.setLineDash([]);
			}
		}

		//画点
		if (opts.dataPointShape !== false) {
			drawPointShape(points, eachSeries.color, eachSeries.pointShape, context, opts);
		}

	});

	if (opts.dataLabel !== false && process === 1) {
		series.forEach(function(eachSeries, seriesIndex) {
			let ranges, minRange, maxRange;
			ranges = [].concat(opts.chartData.yAxisData.ranges[eachSeries.index]);
			minRange = ranges.pop();
			maxRange = ranges.shift();
			var data = eachSeries.data;
			var points = getDataPoints(data, minRange, maxRange, xAxisPoints, eachSpacing, opts, config,
				process);
			drawPointText(points, eachSeries, config, context);
		});
	}

	context.restore();

	return {
		xAxisPoints: xAxisPoints,
		calPoints: calPoints,
		eachSpacing: eachSpacing
	};
}

function drawLineDataPoints(series, opts, config, context) {
	var process = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 1;
	var lineOption = assign({}, {
		type: 'straight',
		width: 2
	}, opts.extra.line);
	lineOption.width *= opts.pixelRatio;

	let xAxisData = opts.chartData.xAxisData,
		xAxisPoints = xAxisData.xAxisPoints,
		eachSpacing = xAxisData.eachSpacing;
	var calPoints = [];

	context.save();
	let leftSpace = 0;
	let rightSpace = opts.width + eachSpacing;
	if (opts._scrollDistance_ && opts._scrollDistance_ !== 0 && opts.enableScroll === true) {
		context.translate(opts._scrollDistance_, 0);
		leftSpace = -opts._scrollDistance_ - eachSpacing + opts.area[3];
		rightSpace = leftSpace + (opts.xAxis.itemCount + 4) * eachSpacing;
	}

	series.forEach(function(eachSeries, seriesIndex) {
		let ranges, minRange, maxRange;
		ranges = [].concat(opts.chartData.yAxisData.ranges[eachSeries.index]);
		minRange = ranges.pop();
		maxRange = ranges.shift();
		var data = eachSeries.data;
		var points = getDataPoints(data, minRange, maxRange, xAxisPoints, eachSpacing, opts, config, process);
		calPoints.push(points);
		var splitPointList = splitPoints(points);

		if (eachSeries.lineType == 'dash') {
			let dashLength = eachSeries.dashLength ? eachSeries.dashLength : 8;
			dashLength *= opts.pixelRatio;
			context.setLineDash([dashLength, dashLength]);
		}
		context.beginPath();
		context.setStrokeStyle(eachSeries.color);
		context.setLineWidth(lineOption.width);

		splitPointList.forEach(function(points, index) {

			if (points.length === 1) {
				context.moveTo(points[0].x, points[0].y);
				context.arc(points[0].x, points[0].y, 1, 0, 2 * Math.PI);
			} else {
				context.moveTo(points[0].x, points[0].y);
				let startPoint = 0;
				if (lineOption.type === 'curve') {
					for (let j = 0; j < points.length; j++) {
						let item = points[j];
						if (startPoint == 0 && item.x > leftSpace) {
							context.moveTo(item.x, item.y);
							startPoint = 1;
						}
						if (j > 0 && item.x > leftSpace && item.x < rightSpace) {
							var ctrlPoint = createCurveControlPoints(points, j - 1);
							context.bezierCurveTo(ctrlPoint.ctrA.x, ctrlPoint.ctrA.y, ctrlPoint.ctrB.x,
								ctrlPoint.ctrB.y, item.x, item.y);
						}
					};
				} else {
					for (let j = 0; j < points.length; j++) {
						let item = points[j];
						if (startPoint == 0 && item.x > leftSpace) {
							context.moveTo(item.x, item.y);
							startPoint = 1;
						}
						if (j > 0 && item.x > leftSpace && item.x < rightSpace) {
							context.lineTo(item.x, item.y);
						}
					};
				}
				context.moveTo(points[0].x, points[0].y);
			}

		});

		context.stroke();
		context.setLineDash([]);

		if (opts.dataPointShape !== false) {
			drawPointShape(points, eachSeries.color, eachSeries.pointShape, context, opts);
		}
	});

	if (opts.dataLabel !== false && process === 1) {
		series.forEach(function(eachSeries, seriesIndex) {
			let ranges, minRange, maxRange;
			ranges = [].concat(opts.chartData.yAxisData.ranges[eachSeries.index]);
			minRange = ranges.pop();
			maxRange = ranges.shift();
			var data = eachSeries.data;
			var points = getDataPoints(data, minRange, maxRange, xAxisPoints, eachSpacing, opts, config,
				process);
			drawPointText(points, eachSeries, config, context);
		});
	}

	context.restore();

	return {
		xAxisPoints: xAxisPoints,
		calPoints: calPoints,
		eachSpacing: eachSpacing
	};
}

function drawMixDataPoints(series, opts, config, context) {
	let process = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 1;

	let xAxisData = opts.chartData.xAxisData,
		xAxisPoints = xAxisData.xAxisPoints,
		eachSpacing = xAxisData.eachSpacing;

	let endY = opts.height - opts.area[2];
	let calPoints = [];

	var columnIndex = 0;
	var columnLength = 0;
	series.forEach(function(eachSeries, seriesIndex) {
		if (eachSeries.type == 'column') {
			columnLength += 1;
		}
	});
	context.save();
	let leftNum = -2;
	let rightNum = xAxisPoints.length + 2;
	let leftSpace = 0;
	let rightSpace = opts.width + eachSpacing;
	if (opts._scrollDistance_ && opts._scrollDistance_ !== 0 && opts.enableScroll === true) {
		context.translate(opts._scrollDistance_, 0);
		leftNum = Math.floor(-opts._scrollDistance_ / eachSpacing) - 2;
		rightNum = leftNum + opts.xAxis.itemCount + 4;
		leftSpace = -opts._scrollDistance_ - eachSpacing + opts.area[3];
		rightSpace = leftSpace + (opts.xAxis.itemCount + 4) * eachSpacing;
	}

	series.forEach(function(eachSeries, seriesIndex) {
		let ranges, minRange, maxRange;

		ranges = [].concat(opts.chartData.yAxisData.ranges[eachSeries.index]);
		minRange = ranges.pop();
		maxRange = ranges.shift();

		var data = eachSeries.data;
		var points = getDataPoints(data, minRange, maxRange, xAxisPoints, eachSpacing, opts, config, process);
		calPoints.push(points);

		// 绘制柱状数据图
		if (eachSeries.type == 'column') {
			points = fixColumeData(points, eachSpacing, columnLength, columnIndex, config, opts);
			for (let i = 0; i < points.length; i++) {
				let item = points[i];
				if (item !== null && i > leftNum && i < rightNum) {
					context.beginPath();
					context.setStrokeStyle(item.color || eachSeries.color);
					context.setLineWidth(1)
					context.setFillStyle(item.color || eachSeries.color);
					var startX = item.x - item.width / 2;
					var height = opts.height - item.y - opts.area[2];
					context.moveTo(startX, item.y);
					context.moveTo(startX, item.y);
					context.lineTo(startX + item.width - 2, item.y);
					context.lineTo(startX + item.width - 2, opts.height - opts.area[2]);
					context.lineTo(startX, opts.height - opts.area[2]);
					context.lineTo(startX, item.y);
					context.closePath();
					context.stroke();
					context.fill();
					context.closePath();
					context.fill();
				}
			}
			columnIndex += 1;
		}

		//绘制区域图数据

		if (eachSeries.type == 'area') {
			let splitPointList = splitPoints(points);
			for (let i = 0; i < splitPointList.length; i++) {
				let points = splitPointList[i];
				// 绘制区域数据
				context.beginPath();
				context.setStrokeStyle(eachSeries.color);
				context.setFillStyle(hexToRgb(eachSeries.color, 0.2));
				context.setLineWidth(2 * opts.pixelRatio);
				if (points.length > 1) {
					var firstPoint = points[0];
					let lastPoint = points[points.length - 1];
					context.moveTo(firstPoint.x, firstPoint.y);
					let startPoint = 0;
					if (eachSeries.style === 'curve') {
						for (let j = 0; j < points.length; j++) {
							let item = points[j];
							if (startPoint == 0 && item.x > leftSpace) {
								context.moveTo(item.x, item.y);
								startPoint = 1;
							}
							if (j > 0 && item.x > leftSpace && item.x < rightSpace) {
								var ctrlPoint = createCurveControlPoints(points, j - 1);
								context.bezierCurveTo(ctrlPoint.ctrA.x, ctrlPoint.ctrA.y, ctrlPoint.ctrB.x,
									ctrlPoint.ctrB.y, item.x, item.y);
							}
						};
					} else {
						for (let j = 0; j < points.length; j++) {
							let item = points[j];
							if (startPoint == 0 && item.x > leftSpace) {
								context.moveTo(item.x, item.y);
								startPoint = 1;
							}
							if (j > 0 && item.x > leftSpace && item.x < rightSpace) {
								context.lineTo(item.x, item.y);
							}
						};
					}
					context.lineTo(lastPoint.x, endY);
					context.lineTo(firstPoint.x, endY);
					context.lineTo(firstPoint.x, firstPoint.y);
				} else {
					let item = points[0];
					context.moveTo(item.x - eachSpacing / 2, item.y);
					context.lineTo(item.x + eachSpacing / 2, item.y);
					context.lineTo(item.x + eachSpacing / 2, endY);
					context.lineTo(item.x - eachSpacing / 2, endY);
					context.moveTo(item.x - eachSpacing / 2, item.y);
				}
				context.closePath();
				context.fill();
			}
		}

		// 绘制折线数据图
		if (eachSeries.type == 'line') {
			var splitPointList = splitPoints(points);
			splitPointList.forEach(function(points, index) {
				if (eachSeries.lineType == 'dash') {
					let dashLength = eachSeries.dashLength ? eachSeries.dashLength : 8;
					dashLength *= opts.pixelRatio;
					context.setLineDash([dashLength, dashLength]);
				}
				context.beginPath();
				context.setStrokeStyle(eachSeries.color);
				context.setLineWidth(2 * opts.pixelRatio);
				if (points.length === 1) {
					context.moveTo(points[0].x, points[0].y);
					context.arc(points[0].x, points[0].y, 1, 0, 2 * Math.PI);
				} else {
					context.moveTo(points[0].x, points[0].y);
					let startPoint = 0;
					if (eachSeries.style == 'curve') {
						for (let j = 0; j < points.length; j++) {
							let item = points[j];
							if (startPoint == 0 && item.x > leftSpace) {
								context.moveTo(item.x, item.y);
								startPoint = 1;
							}
							if (j > 0 && item.x > leftSpace && item.x < rightSpace) {
								var ctrlPoint = createCurveControlPoints(points, j - 1);
								context.bezierCurveTo(ctrlPoint.ctrA.x, ctrlPoint.ctrA.y, ctrlPoint.ctrB
									.x, ctrlPoint.ctrB.y, item.x, item.y);
							}
						}
					} else {
						for (let j = 0; j < points.length; j++) {
							let item = points[j];
							if (startPoint == 0 && item.x > leftSpace) {
								context.moveTo(item.x, item.y);
								startPoint = 1;
							}
							if (j > 0 && item.x > leftSpace && item.x < rightSpace) {
								context.lineTo(item.x, item.y);
							}
						}
					}
					context.moveTo(points[0].x, points[0].y);
				}
				context.stroke();
				context.setLineDash([]);
			});
		}

		// 绘制点数据图
		if (eachSeries.type == 'point') {
			eachSeries.addPoint = true;
		}

		if (eachSeries.addPoint == true && eachSeries.type !== 'column') {
			drawPointShape(points, eachSeries.color, eachSeries.pointShape, context, opts);
		}
	});
	if (opts.dataLabel !== false && process === 1) {
		var columnIndex = 0;
		series.forEach(function(eachSeries, seriesIndex) {
			let ranges, minRange, maxRange;

			ranges = [].concat(opts.chartData.yAxisData.ranges[eachSeries.index]);
			minRange = ranges.pop();
			maxRange = ranges.shift();

			var data = eachSeries.data;
			var points = getDataPoints(data, minRange, maxRange, xAxisPoints, eachSpacing, opts, config,
				process);
			if (eachSeries.type !== 'column') {
				drawPointText(points, eachSeries, config, context);
			} else {
				points = fixColumeData(points, eachSpacing, columnLength, columnIndex, config, opts);
				drawPointText(points, eachSeries, config, context);
				columnIndex += 1;
			}

		});
	}

	context.restore();

	return {
		xAxisPoints: xAxisPoints,
		calPoints: calPoints,
		eachSpacing: eachSpacing,
	}
}

function drawToolTipBridge(opts, config, context, process, eachSpacing, xAxisPoints) {
	var toolTipOption = opts.extra.tooltip || {};
	if (toolTipOption.horizentalLine && opts.tooltip && process === 1 && (opts.type == 'line' || opts.type == 'area' ||
			opts.type == 'column' || opts.type == 'candle' || opts.type == 'mix')) {
		drawToolTipHorizentalLine(opts, config, context, eachSpacing, xAxisPoints)
	}
	context.save();
	if (opts._scrollDistance_ && opts._scrollDistance_ !== 0 && opts.enableScroll === true) {
		context.translate(opts._scrollDistance_, 0);
	}
	if (opts.tooltip && opts.tooltip.textList && opts.tooltip.textList.length && process === 1) {
		drawToolTip(opts.tooltip.textList, opts.tooltip.offset, opts, config, context, eachSpacing, xAxisPoints);
	}
	context.restore();

}

function drawXAxis(categories, opts, config, context) {

	let xAxisData = opts.chartData.xAxisData,
		xAxisPoints = xAxisData.xAxisPoints,
		startX = xAxisData.startX,
		endX = xAxisData.endX,
		eachSpacing = xAxisData.eachSpacing;
	var boundaryGap = 'center';
	if (opts.type == 'line' || opts.type == 'area') {
		boundaryGap = opts.xAxis.boundaryGap;
	}
	var startY = opts.height - opts.area[2];
	var endY = opts.area[0];

	//绘制滚动条
	if (opts.enableScroll && opts.xAxis.scrollShow) {
		var scrollY = opts.height - opts.area[2] + config.xAxisHeight;
		var scrollScreenWidth = endX - startX;
		var scrollTotalWidth = eachSpacing * (xAxisPoints.length - 1);
		var scrollWidth = scrollScreenWidth * scrollScreenWidth / scrollTotalWidth;
		var scrollLeft = 0;
		if (opts._scrollDistance_) {
			scrollLeft = -opts._scrollDistance_ * (scrollScreenWidth) / scrollTotalWidth;
		}
		context.beginPath();
		context.setLineCap('round');
		context.setLineWidth(6 * opts.pixelRatio);
		context.setStrokeStyle(opts.xAxis.scrollBackgroundColor || "#EFEBEF");
		context.moveTo(startX, scrollY);
		context.lineTo(endX, scrollY);
		context.stroke();
		context.closePath();
		context.beginPath();
		context.setLineCap('round');
		context.setLineWidth(6 * opts.pixelRatio);
		context.setStrokeStyle(opts.xAxis.scrollColor || "#A6A6A6");
		context.moveTo(startX + scrollLeft, scrollY);
		context.lineTo(startX + scrollLeft + scrollWidth, scrollY);
		context.stroke();
		context.closePath();
		context.setLineCap('butt');
	}

	context.save();

	if (opts._scrollDistance_ && opts._scrollDistance_ !== 0) {
		context.translate(opts._scrollDistance_, 0);
	}

	//绘制X轴刻度线
	if (opts.xAxis.calibration === true) {
		context.setStrokeStyle(opts.xAxis.gridColor || "#cccccc");
		context.setLineCap('butt');
		context.setLineWidth(1 * opts.pixelRatio);
		xAxisPoints.forEach(function(item, index) {
			if (index > 0) {
				context.beginPath();
				context.moveTo(item - eachSpacing / 2, startY);
				context.lineTo(item - eachSpacing / 2, startY + 3 * opts.pixelRatio);
				context.closePath();
				context.stroke();
			}
		});
	}
	//绘制X轴网格
	if (opts.xAxis.disableGrid !== true) {
		context.setStrokeStyle(opts.xAxis.gridColor || "#cccccc");
		context.setLineCap('butt');
		context.setLineWidth(1 * opts.pixelRatio);
		if (opts.xAxis.gridType == 'dash') {
			context.setLineDash([opts.xAxis.dashLength, opts.xAxis.dashLength]);
		}
		opts.xAxis.gridEval = opts.xAxis.gridEval || 1;
		xAxisPoints.forEach(function(item, index) {
			if (index % opts.xAxis.gridEval == 0) {
				context.beginPath();
				context.moveTo(item, startY);
				context.lineTo(item, endY);
				context.stroke();
			}
		});
		context.setLineDash([]);
	}


	//绘制X轴文案
	if (opts.xAxis.disabled !== true) {
		// 对X轴列表做抽稀处理
		//默认全部显示X轴标签
		let maxXAxisListLength = categories.length;
		//如果设置了X轴单屏数量
		if (opts.xAxis.labelCount) {
			//如果设置X轴密度
			if (opts.xAxis.itemCount) {
				maxXAxisListLength = Math.ceil(categories.length / opts.xAxis.itemCount * opts.xAxis.labelCount);
			} else {
				maxXAxisListLength = opts.xAxis.labelCount;
			}
			maxXAxisListLength -= 1;
		}

		let ratio = Math.ceil(categories.length / maxXAxisListLength);

		let newCategories = [];
		let cgLength = categories.length;
		for (let i = 0; i < cgLength; i++) {
			if (i % ratio !== 0) {
				newCategories.push("");
			} else {
				newCategories.push(categories[i]);
			}
		}
		newCategories[cgLength - 1] = categories[cgLength - 1];

		var xAxisFontSize = opts.xAxis.fontSize || config.fontSize;
		if (config._xAxisTextAngle_ === 0) {
			newCategories.forEach(function(item, index) {
				var offset = -measureText(String(item), xAxisFontSize) / 2;
				if (boundaryGap == 'center') {
					offset += eachSpacing / 2;
				}
				var scrollHeight = 0;
				if (opts.xAxis.scrollShow) {
					scrollHeight = 6 * opts.pixelRatio;
				}
				context.beginPath();
				context.setFontSize(xAxisFontSize);
				context.setFillStyle(opts.xAxis.fontColor || '#666666');
				context.fillText(String(item), xAxisPoints[index] + offset, startY + xAxisFontSize + (config
					.xAxisHeight - scrollHeight - xAxisFontSize) / 2);
				context.closePath();
				context.stroke();
			});

		} else {
			newCategories.forEach(function(item, index) {
				context.save();
				context.beginPath();
				context.setFontSize(xAxisFontSize);
				context.setFillStyle(opts.xAxis.fontColor || '#666666');
				var textWidth = measureText(String(item), xAxisFontSize);
				var offset = -textWidth;
				if (boundaryGap == 'center') {
					offset += eachSpacing / 2;
				}
				var _calRotateTranslate = calRotateTranslate(xAxisPoints[index] + eachSpacing / 2, startY +
						xAxisFontSize / 2 + 5, opts.height),
					transX = _calRotateTranslate.transX,
					transY = _calRotateTranslate.transY;

				context.rotate(-1 * config._xAxisTextAngle_);
				context.translate(transX, transY);
				context.fillText(String(item), xAxisPoints[index] + offset, startY + xAxisFontSize + 5);
				context.closePath();
				context.stroke();
				context.restore();
			});
		}
	}
	context.restore();

	//绘制X轴轴线
	if (opts.xAxis.axisLine) {
		context.beginPath();
		context.setStrokeStyle(opts.xAxis.axisLineColor);
		context.setLineWidth(1 * opts.pixelRatio);
		context.moveTo(startX, opts.height - opts.area[2]);
		context.lineTo(endX, opts.height - opts.area[2]);
		context.stroke();
	}
}

function drawYAxisGrid(categories, opts, config, context) {
	if (opts.yAxis.disableGrid === true) {
		return;
	}
	let spacingValid = opts.height - opts.area[0] - opts.area[2];
	let eachSpacing = spacingValid / opts.yAxis.splitNumber;
	let startX = opts.area[3];
	let xAxisPoints = opts.chartData.xAxisData.xAxisPoints,
		xAxiseachSpacing = opts.chartData.xAxisData.eachSpacing;
	let TotalWidth = xAxiseachSpacing * (xAxisPoints.length - 1);
	let endX = startX + TotalWidth;

	let points = [];
	for (let i = 0; i < opts.yAxis.splitNumber + 1; i++) {
		points.push(opts.height - opts.area[2] - eachSpacing * i);
	}

	context.save();
	if (opts._scrollDistance_ && opts._scrollDistance_ !== 0) {
		context.translate(opts._scrollDistance_, 0);
	}

	if (opts.yAxis.gridType == 'dash') {
		context.setLineDash([opts.yAxis.dashLength, opts.yAxis.dashLength]);
	}
	context.setStrokeStyle(opts.yAxis.gridColor);
	context.setLineWidth(1 * opts.pixelRatio);
	points.forEach(function(item, index) {
		context.beginPath();
		context.moveTo(startX, item);
		context.lineTo(endX, item);
		context.stroke();
	});
	context.setLineDash([]);

	context.restore();
}

function drawYAxis(series, opts, config, context) {
	if (opts.yAxis.disabled === true) {
		return;
	}
	var spacingValid = opts.height - opts.area[0] - opts.area[2];
	var eachSpacing = spacingValid / opts.yAxis.splitNumber;
	var startX = opts.area[3];
	var endX = opts.width - opts.area[1];
	var endY = opts.height - opts.area[2];
	var fillEndY = endY + config.xAxisHeight;
	if (opts.xAxis.scrollShow) {
		fillEndY -= 3 * opts.pixelRatio;
	}
	if (opts.xAxis.rotateLabel) {
		fillEndY = opts.height - opts.area[2] + 3;
	}
	// set YAxis background
	context.beginPath();
	context.setFillStyle(opts.background || '#ffffff');
	if (opts._scrollDistance_ < 0) {
		context.fillRect(0, 0, startX, fillEndY);
	}
	if (opts.enableScroll == true) {
		context.fillRect(endX, 0, opts.width, fillEndY);
	}
	context.closePath();
	context.stroke();

	var points = [];
	for (let i = 0; i <= opts.yAxis.splitNumber; i++) {
		points.push(opts.area[0] + eachSpacing * i);
	}

	let tStartLeft = opts.area[3];
	let tStartRight = opts.width - opts.area[1];

	for (let i = 0; i < opts.yAxis.data.length; i++) {
		let yData = opts.yAxis.data[i];
		if (yData.disabled !== true) {
			let rangesFormat = opts.chartData.yAxisData.rangesFormat[i];
			let yAxisFontSize = yData.fontSize || config.fontSize;
			let yAxisWidth = opts.chartData.yAxisData.yAxisWidth[i];
			//画Y轴刻度及文案
			rangesFormat.forEach(function(item, index) {
				var pos = points[index] ? points[index] : endY;
				context.beginPath();
				context.setFontSize(yAxisFontSize);
				context.setLineWidth(1 * opts.pixelRatio);
				context.setStrokeStyle(yData.axisLineColor || '#cccccc');
				context.setFillStyle(yData.fontColor || '#666666');
				if (yAxisWidth.position == 'left') {
					context.fillText(String(item), tStartLeft - yAxisWidth.width, pos + yAxisFontSize / 2);
					//画刻度线
					if (yData.calibration == true) {
						context.moveTo(tStartLeft, pos);
						context.lineTo(tStartLeft - 3 * opts.pixelRatio, pos);
					}
				} else {
					context.fillText(String(item), tStartRight + 4 * opts.pixelRatio, pos + yAxisFontSize / 2);
					//画刻度线
					if (yData.calibration == true) {
						context.moveTo(tStartRight, pos);
						context.lineTo(tStartRight + 3 * opts.pixelRatio, pos);
					}
				}
				context.closePath();
				context.stroke();
			});
			//画Y轴轴线
			if (yData.axisLine !== false) {
				context.beginPath();
				context.setStrokeStyle(yData.axisLineColor || '#cccccc');
				context.setLineWidth(1 * opts.pixelRatio);
				if (yAxisWidth.position == 'left') {
					context.moveTo(tStartLeft, opts.height - opts.area[2]);
					context.lineTo(tStartLeft, opts.area[0]);
				} else {
					context.moveTo(tStartRight, opts.height - opts.area[2]);
					context.lineTo(tStartRight, opts.area[0]);
				}
				context.stroke();
			}

			//画Y轴标题
			if (opts.yAxis.showTitle) {

				let titleFontSize = yData.titleFontSize || config.fontSize;
				let title = yData.title;
				context.beginPath();
				context.setFontSize(titleFontSize);
				context.setFillStyle(yData.titleFontColor || '#666666');
				if (yAxisWidth.position == 'left') {
					context.fillText(title, tStartLeft - measureText(title, titleFontSize) / 2, opts.area[0] - 10 * opts
						.pixelRatio);
				} else {
					context.fillText(title, tStartRight - measureText(title, titleFontSize) / 2, opts.area[0] - 10 *
						opts.pixelRatio);
				}
				context.closePath();
				context.stroke();
			}
			if (yAxisWidth.position == 'left') {
				tStartLeft -= (yAxisWidth.width + opts.yAxis.padding);
			} else {
				tStartRight += yAxisWidth.width + opts.yAxis.padding;
			}
		}
	}
}

function drawLegend(series, opts, config, context, chartData) {
	if (opts.legend.show === false) {
		return;
	}
	let legendData = chartData.legendData;
	let legendList = legendData.points;
	let legendArea = legendData.area;
	let padding = opts.legend.padding;
	let fontSize = opts.legend.fontSize;
	let shapeWidth = 15 * opts.pixelRatio;
	let shapeRight = 5 * opts.pixelRatio;
	let itemGap = opts.legend.itemGap;
	let lineHeight = Math.max(opts.legend.lineHeight * opts.pixelRatio, fontSize);

	//画背景及边框
	context.beginPath();
	context.setLineWidth(opts.legend.borderWidth);
	context.setStrokeStyle(opts.legend.borderColor);
	context.setFillStyle(opts.legend.backgroundColor);
	context.moveTo(legendArea.start.x, legendArea.start.y);
	context.rect(legendArea.start.x, legendArea.start.y, legendArea.width, legendArea.height);
	context.closePath();
	context.fill();
	context.stroke();

	legendList.forEach(function(itemList, listIndex) {
		let width = 0;
		let height = 0;
		width = legendData.widthArr[listIndex];
		height = legendData.heightArr[listIndex];
		let startX = 0;
		let startY = 0;
		if (opts.legend.position == 'top' || opts.legend.position == 'bottom') {
			startX = legendArea.start.x + (legendArea.width - width) / 2;
			startY = legendArea.start.y + padding + listIndex * lineHeight;
		} else {
			if (listIndex == 0) {
				width = 0;
			} else {
				width = legendData.widthArr[listIndex - 1];
			}
			startX = legendArea.start.x + padding + width;
			startY = legendArea.start.y + padding + (legendArea.height - height) / 2;
		}

		context.setFontSize(config.fontSize);
		for (let i = 0; i < itemList.length; i++) {
			let item = itemList[i];
			item.area = [0, 0, 0, 0];
			item.area[0] = startX;
			item.area[1] = startY;
			item.area[3] = startY + lineHeight;
			context.beginPath();
			context.setLineWidth(1 * opts.pixelRatio);
			context.setStrokeStyle(item.show ? item.color : opts.legend.hiddenColor);
			context.setFillStyle(item.show ? item.color : opts.legend.hiddenColor);
			switch (item.legendShape) {
				case 'line':
					context.moveTo(startX, startY + 0.5 * lineHeight - 2 * opts.pixelRatio);
					context.fillRect(startX, startY + 0.5 * lineHeight - 2 * opts.pixelRatio, 15 * opts
						.pixelRatio, 4 * opts.pixelRatio);
					break;
				case 'triangle':
					context.moveTo(startX + 7.5 * opts.pixelRatio, startY + 0.5 * lineHeight - 5 * opts
						.pixelRatio);
					context.lineTo(startX + 2.5 * opts.pixelRatio, startY + 0.5 * lineHeight + 5 * opts
						.pixelRatio);
					context.lineTo(startX + 12.5 * opts.pixelRatio, startY + 0.5 * lineHeight + 5 * opts
						.pixelRatio);
					context.lineTo(startX + 7.5 * opts.pixelRatio, startY + 0.5 * lineHeight - 5 * opts
						.pixelRatio);
					break;
				case 'diamond':
					context.moveTo(startX + 7.5 * opts.pixelRatio, startY + 0.5 * lineHeight - 5 * opts
						.pixelRatio);
					context.lineTo(startX + 2.5 * opts.pixelRatio, startY + 0.5 * lineHeight);
					context.lineTo(startX + 7.5 * opts.pixelRatio, startY + 0.5 * lineHeight + 5 * opts
						.pixelRatio);
					context.lineTo(startX + 12.5 * opts.pixelRatio, startY + 0.5 * lineHeight);
					context.lineTo(startX + 7.5 * opts.pixelRatio, startY + 0.5 * lineHeight - 5 * opts
						.pixelRatio);
					break;
				case 'circle':
					context.moveTo(startX + 7.5 * opts.pixelRatio, startY + 0.5 * lineHeight);
					context.arc(startX + 7.5 * opts.pixelRatio, startY + 0.5 * lineHeight, 5 * opts.pixelRatio,
						0, 2 * Math.PI);
					break;
				case 'rect':
					context.moveTo(startX, startY + 0.5 * lineHeight - 5 * opts.pixelRatio);
					context.fillRect(startX, startY + 0.5 * lineHeight - 5 * opts.pixelRatio, 15 * opts
						.pixelRatio, 10 * opts.pixelRatio);
					break;
				default:
					context.moveTo(startX, startY + 0.5 * lineHeight - 5 * opts.pixelRatio);
					context.fillRect(startX, startY + 0.5 * lineHeight - 5 * opts.pixelRatio, 15 * opts
						.pixelRatio, 10 * opts.pixelRatio);
			}
			context.closePath();
			context.fill();
			context.stroke();

			startX += shapeWidth + shapeRight;
			let fontTrans = 0.5 * lineHeight + 0.5 * fontSize - 2;
			context.beginPath();
			context.setFontSize(fontSize);
			context.setFillStyle(item.show ? opts.legend.fontColor : opts.legend.hiddenColor);
			context.fillText(item.name, startX, startY + fontTrans);
			context.closePath();
			context.stroke();
			if (opts.legend.position == 'top' || opts.legend.position == 'bottom') {
				startX += measureText(item.name, fontSize) + itemGap;
				item.area[2] = startX;
			} else {
				item.area[2] = startX + measureText(item.name, fontSize) + itemGap;;
				startX -= shapeWidth + shapeRight;
				startY += lineHeight;
			}
		}
	});
}

function drawPieDataPoints(series, opts, config, context) {
	var process = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 1;
	var pieOption = assign({}, {
		activeOpacity: 0.5,
		activeRadius: 10 * opts.pixelRatio,
		offsetAngle: 0,
		labelWidth: 15 * opts.pixelRatio,
		ringWidth: 0,
		border: false,
		borderWidth: 2,
		borderColor: '#FFFFFF'
	}, opts.extra.pie);
	var centerPosition = {
		x: opts.area[3] + (opts.width - opts.area[1] - opts.area[3]) / 2,
		y: opts.area[0] + (opts.height - opts.area[0] - opts.area[2]) / 2
	};
	if (config.pieChartLinePadding == 0) {
		config.pieChartLinePadding = pieOption.activeRadius;
	}

	var radius = Math.min((opts.width - opts.area[1] - opts.area[3]) / 2 - config.pieChartLinePadding - config
		.pieChartTextPadding - config._pieTextMaxLength_, (opts.height - opts.area[0] - opts.area[2]) / 2 - config
		.pieChartLinePadding - config.pieChartTextPadding);

	series = getPieDataPoints(series, radius, process);

	var activeRadius = pieOption.activeRadius;

	series = series.map(function(eachSeries) {
		eachSeries._start_ += (pieOption.offsetAngle) * Math.PI / 180;
		return eachSeries;
	});
	series.forEach(function(eachSeries, seriesIndex) {
		if (opts.tooltip) {
			if (opts.tooltip.index == seriesIndex) {
				context.beginPath();
				context.setFillStyle(hexToRgb(eachSeries.color, opts.extra.pie.activeOpacity || 0.5));
				context.moveTo(centerPosition.x, centerPosition.y);
				context.arc(centerPosition.x, centerPosition.y, eachSeries._radius_ + activeRadius, eachSeries
					._start_,
					eachSeries._start_ + 2 *
					eachSeries._proportion_ * Math.PI);
				context.closePath();
				context.fill();
			}
		}
		context.beginPath();
		context.setLineWidth(pieOption.borderWidth * opts.pixelRatio);
		context.lineJoin = "round";
		context.setStrokeStyle(pieOption.borderColor);
		context.setFillStyle(eachSeries.color);
		context.moveTo(centerPosition.x, centerPosition.y);
		context.arc(centerPosition.x, centerPosition.y, eachSeries._radius_, eachSeries._start_, eachSeries
			._start_ + 2 * eachSeries._proportion_ * Math.PI);
		context.closePath();
		context.fill();
		if (pieOption.border == true) {
			context.stroke();
		}
	});

	if (opts.type === 'ring') {
		var innerPieWidth = radius * 0.6;
		if (typeof opts.extra.pie.ringWidth === 'number' && opts.extra.pie.ringWidth > 0) {
			innerPieWidth = Math.max(0, radius - opts.extra.pie.ringWidth);
		}
		context.beginPath();
		context.setFillStyle(opts.background || '#ffffff');
		context.moveTo(centerPosition.x, centerPosition.y);
		context.arc(centerPosition.x, centerPosition.y, innerPieWidth, 0, 2 * Math.PI);
		context.closePath();
		context.fill();
	}

	if (opts.dataLabel !== false && process === 1) {
		var valid = false;
		for (var i = 0, len = series.length; i < len; i++) {
			if (series[i].data > 0) {
				valid = true;
				break;
			}
		}

		if (valid) {
			drawPieText(series, opts, config, context, radius, centerPosition);
		}
	}

	if (process === 1 && opts.type === 'ring') {
		drawRingTitle(opts, config, context, centerPosition);
	}

	return {
		center: centerPosition,
		radius: radius,
		series: series
	};
}

function drawRoseDataPoints(series, opts, config, context) {
	var process = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 1;
	var roseOption = assign({}, {
		type: 'area',
		activeOpacity: 0.5,
		activeRadius: 10 * opts.pixelRatio,
		offsetAngle: 0,
		labelWidth: 15 * opts.pixelRatio,
		border: false,
		borderWidth: 2,
		borderColor: '#FFFFFF'
	}, opts.extra.rose);
	if (config.pieChartLinePadding == 0) {
		config.pieChartLinePadding = roseOption.activeRadius;
	}
	var centerPosition = {
		x: opts.area[3] + (opts.width - opts.area[1] - opts.area[3]) / 2,
		y: opts.area[0] + (opts.height - opts.area[0] - opts.area[2]) / 2
	};
	var radius = Math.min((opts.width - opts.area[1] - opts.area[3]) / 2 - config.pieChartLinePadding - config
		.pieChartTextPadding - config._pieTextMaxLength_, (opts.height - opts.area[0] - opts.area[2]) / 2 - config
		.pieChartLinePadding - config.pieChartTextPadding);
	var minRadius = roseOption.minRadius || radius * 0.5;

	series = getRoseDataPoints(series, roseOption.type, minRadius, radius, process);

	var activeRadius = roseOption.activeRadius;

	series = series.map(function(eachSeries) {
		eachSeries._start_ += (roseOption.offsetAngle || 0) * Math.PI / 180;
		return eachSeries;
	});

	series.forEach(function(eachSeries, seriesIndex) {
		if (opts.tooltip) {
			if (opts.tooltip.index == seriesIndex) {
				context.beginPath();
				context.setFillStyle(hexToRgb(eachSeries.color, roseOption.activeOpacity || 0.5));
				context.moveTo(centerPosition.x, centerPosition.y);
				context.arc(centerPosition.x, centerPosition.y, activeRadius + eachSeries._radius_, eachSeries
					._start_,
					eachSeries._start_ + 2 * eachSeries._rose_proportion_ * Math.PI);
				context.closePath();
				context.fill();
			}
		}
		context.beginPath();
		context.setLineWidth(roseOption.borderWidth * opts.pixelRatio);
		context.lineJoin = "round";
		context.setStrokeStyle(roseOption.borderColor);
		context.setFillStyle(eachSeries.color);
		context.moveTo(centerPosition.x, centerPosition.y);
		context.arc(centerPosition.x, centerPosition.y, eachSeries._radius_, eachSeries._start_, eachSeries
			._start_ + 2 *
			eachSeries._rose_proportion_ * Math.PI);
		context.closePath();
		context.fill();
		if (roseOption.border == true) {
			context.stroke();
		}
	});

	if (opts.dataLabel !== false && process === 1) {
		var valid = false;
		for (var i = 0, len = series.length; i < len; i++) {
			if (series[i].data > 0) {
				valid = true;
				break;
			}
		}

		if (valid) {
			drawPieText(series, opts, config, context, radius, centerPosition);
		}
	}

	return {
		center: centerPosition,
		radius: radius,
		series: series
	};
}

function drawArcbarDataPoints(series, opts, config, context) {
	var process = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 1;
	var arcbarOption = assign({}, {
		startAngle: 0.75,
		endAngle: 0.25,
		type: 'default',
		width: 12 * opts.pixelRatio,
		gap: 2 * opts.pixelRatio
	}, opts.extra.arcbar);

	series = getArcbarDataPoints(series, arcbarOption, process);

	var centerPosition;
	if (arcbarOption.center) {
		centerPosition = arcbarOption.center;
	} else {
		centerPosition = {
			x: opts.width / 2,
			y: opts.height / 2
		};
	}

	var radius;
	if (arcbarOption.radius) {
		radius = arcbarOption.radius;
	} else {
		radius = Math.min(centerPosition.x, centerPosition.y);
		radius -= 5 * opts.pixelRatio;
		radius -= arcbarOption.width / 2;
	}

	for (let i = 0; i < series.length; i++) {
		let eachSeries = series[i];
		//背景颜色
		context.setLineWidth(arcbarOption.width);
		context.setStrokeStyle(arcbarOption.backgroundColor || '#E9E9E9');
		context.setLineCap('round');
		context.beginPath();
		if (arcbarOption.type == 'default') {
			context.arc(centerPosition.x, centerPosition.y, radius - (arcbarOption.width + arcbarOption.gap) * i,
				arcbarOption.startAngle * Math.PI, arcbarOption.endAngle * Math.PI, false);
		} else {
			context.arc(centerPosition.x, centerPosition.y, radius - (arcbarOption.width + arcbarOption.gap) * i, 0, 2 *
				Math.PI, false);
		}
		context.stroke();
		//进度条
		context.setLineWidth(arcbarOption.width);
		context.setStrokeStyle(eachSeries.color);
		context.setLineCap('round');
		context.beginPath();
		context.arc(centerPosition.x, centerPosition.y, radius - (arcbarOption.width + arcbarOption.gap) * i,
			arcbarOption.startAngle * Math.PI, eachSeries._proportion_ * Math.PI, false);
		context.stroke();
	}

	drawRingTitle(opts, config, context, centerPosition);

	return {
		center: centerPosition,
		radius: radius,
		series: series
	};
}

function drawGaugeDataPoints(categories, series, opts, config, context) {
	var process = arguments.length > 5 && arguments[5] !== undefined ? arguments[5] : 1;
	var gaugeOption = assign({}, {
		type: 'default',
		startAngle: 0.75,
		endAngle: 0.25,
		width: 15,
		splitLine: {
			fixRadius: 0,
			splitNumber: 10,
			width: 15,
			color: '#FFFFFF',
			childNumber: 5,
			childWidth: 5
		},
		pointer: {
			width: 15,
			color: 'auto'
		}
	}, opts.extra.gauge);

	if (gaugeOption.oldAngle == undefined) {
		gaugeOption.oldAngle = gaugeOption.startAngle;
	}
	if (gaugeOption.oldData == undefined) {
		gaugeOption.oldData = 0;
	}
	categories = getGaugeAxisPoints(categories, gaugeOption.startAngle, gaugeOption.endAngle);

	var centerPosition = {
		x: opts.width / 2,
		y: opts.height / 2
	};
	var radius = Math.min(centerPosition.x, centerPosition.y);
	radius -= 5 * opts.pixelRatio;
	radius -= gaugeOption.width / 2;
	var innerRadius = radius - gaugeOption.width;
	var totalAngle = 0;

	//判断仪表盘的样式：default百度样式，progress新样式
	if (gaugeOption.type == 'progress') {

		//## 第一步画中心圆形背景和进度条背景
		//中心圆形背景
		var pieRadius = radius - gaugeOption.width * 3;
		context.beginPath();
		let gradient = context.createLinearGradient(centerPosition.x, centerPosition.y - pieRadius, centerPosition.x,
			centerPosition.y + pieRadius);
		//配置渐变填充（起点：中心点向上减半径；结束点中心点向下加半径）
		gradient.addColorStop('0', hexToRgb(series[0].color, 0.3));
		gradient.addColorStop('1.0', hexToRgb("#FFFFFF", 0.1));
		context.setFillStyle(gradient);
		context.arc(centerPosition.x, centerPosition.y, pieRadius, 0, 2 * Math.PI, false);
		context.fill();
		//画进度条背景
		context.setLineWidth(gaugeOption.width);
		context.setStrokeStyle(hexToRgb(series[0].color, 0.3));
		context.setLineCap('round');
		context.beginPath();
		context.arc(centerPosition.x, centerPosition.y, innerRadius, gaugeOption.startAngle * Math.PI, gaugeOption
			.endAngle * Math.PI, false);
		context.stroke();

		//## 第二步画刻度线
		totalAngle = gaugeOption.startAngle - gaugeOption.endAngle + 1;
		let splitAngle = totalAngle / gaugeOption.splitLine.splitNumber;
		let childAngle = totalAngle / gaugeOption.splitLine.splitNumber / gaugeOption.splitLine.childNumber;
		let startX = -radius - gaugeOption.width * 0.5 - gaugeOption.splitLine.fixRadius;
		let endX = -radius - gaugeOption.width - gaugeOption.splitLine.fixRadius + gaugeOption.splitLine.width;
		context.save();
		context.translate(centerPosition.x, centerPosition.y);
		context.rotate((gaugeOption.startAngle - 1) * Math.PI);
		let len = gaugeOption.splitLine.splitNumber * gaugeOption.splitLine.childNumber + 1;
		let proc = series[0].data * process;
		for (let i = 0; i < len; i++) {
			context.beginPath();
			//刻度线随进度变色
			if (proc > (i / len)) {
				context.setStrokeStyle(hexToRgb(series[0].color, 1));
			} else {
				context.setStrokeStyle(hexToRgb(series[0].color, 0.3));
			}
			context.setLineWidth(3 * opts.pixelRatio);
			context.moveTo(startX, 0);
			context.lineTo(endX, 0);
			context.stroke();
			context.rotate(childAngle * Math.PI);
		}
		context.restore();

		//## 第三步画进度条
		series = getArcbarDataPoints(series, gaugeOption, process);
		context.setLineWidth(gaugeOption.width);
		context.setStrokeStyle(series[0].color);
		context.setLineCap('round');
		context.beginPath();
		context.arc(centerPosition.x, centerPosition.y, innerRadius, gaugeOption.startAngle * Math.PI, series[0]
			._proportion_ * Math.PI, false);
		context.stroke();

		//## 第四步画指针
		let pointerRadius = radius - gaugeOption.width * 2.5;
		context.save();
		context.translate(centerPosition.x, centerPosition.y);
		context.rotate((series[0]._proportion_ - 1) * Math.PI);
		context.beginPath();
		context.setLineWidth(gaugeOption.width / 3);
		let gradient3 = context.createLinearGradient(0, -pointerRadius * 0.6, 0, pointerRadius * 0.6);
		gradient3.addColorStop('0', hexToRgb('#FFFFFF', 0));
		gradient3.addColorStop('0.5', hexToRgb(series[0].color, 1));
		gradient3.addColorStop('1.0', hexToRgb('#FFFFFF', 0));
		context.setStrokeStyle(gradient3);
		context.arc(0, 0, pointerRadius, 0.85 * Math.PI, 1.15 * Math.PI, false);
		context.stroke();
		context.beginPath();
		context.setLineWidth(1);
		context.setStrokeStyle(series[0].color);
		context.setFillStyle(series[0].color);
		context.moveTo(-pointerRadius - gaugeOption.width / 3 / 2, -4);
		context.lineTo(-pointerRadius - gaugeOption.width / 3 / 2 - 4, 0);
		context.lineTo(-pointerRadius - gaugeOption.width / 3 / 2, 4);
		context.lineTo(-pointerRadius - gaugeOption.width / 3 / 2, -4);
		context.stroke();
		context.fill();
		context.restore();

		//default百度样式
	} else {
		//画背景
		context.setLineWidth(gaugeOption.width);
		context.setLineCap('butt');
		for (let i = 0; i < categories.length; i++) {
			let eachCategories = categories[i];
			context.beginPath();
			context.setStrokeStyle(eachCategories.color);
			context.arc(centerPosition.x, centerPosition.y, radius, eachCategories._startAngle_ * Math.PI,
				eachCategories._endAngle_ * Math.PI, false);
			context.stroke();
		}
		context.save();

		//画刻度线
		totalAngle = gaugeOption.startAngle - gaugeOption.endAngle + 1;
		let splitAngle = totalAngle / gaugeOption.splitLine.splitNumber;
		let childAngle = totalAngle / gaugeOption.splitLine.splitNumber / gaugeOption.splitLine.childNumber;
		let startX = -radius - gaugeOption.width * 0.5 - gaugeOption.splitLine.fixRadius;
		let endX = -radius - gaugeOption.width * 0.5 - gaugeOption.splitLine.fixRadius + gaugeOption.splitLine.width;
		let childendX = -radius - gaugeOption.width * 0.5 - gaugeOption.splitLine.fixRadius + gaugeOption.splitLine
			.childWidth;

		context.translate(centerPosition.x, centerPosition.y);
		context.rotate((gaugeOption.startAngle - 1) * Math.PI);

		for (let i = 0; i < gaugeOption.splitLine.splitNumber + 1; i++) {
			context.beginPath();
			context.setStrokeStyle(gaugeOption.splitLine.color);
			context.setLineWidth(2 * opts.pixelRatio);
			context.moveTo(startX, 0);
			context.lineTo(endX, 0);
			context.stroke();
			context.rotate(splitAngle * Math.PI);
		}
		context.restore();

		context.save();
		context.translate(centerPosition.x, centerPosition.y);
		context.rotate((gaugeOption.startAngle - 1) * Math.PI);

		for (let i = 0; i < gaugeOption.splitLine.splitNumber * gaugeOption.splitLine.childNumber + 1; i++) {
			context.beginPath();
			context.setStrokeStyle(gaugeOption.splitLine.color);
			context.setLineWidth(1 * opts.pixelRatio);
			context.moveTo(startX, 0);
			context.lineTo(childendX, 0);
			context.stroke();
			context.rotate(childAngle * Math.PI);
		}
		context.restore();

		//画指针
		series = getGaugeDataPoints(series, categories, gaugeOption, process);

		for (let i = 0; i < series.length; i++) {
			let eachSeries = series[i];
			context.save();
			context.translate(centerPosition.x, centerPosition.y);
			context.rotate((eachSeries._proportion_ - 1) * Math.PI);
			context.beginPath();
			context.setFillStyle(eachSeries.color);
			context.moveTo(gaugeOption.pointer.width, 0);
			context.lineTo(0, -gaugeOption.pointer.width / 2);
			context.lineTo(-innerRadius, 0);
			context.lineTo(0, gaugeOption.pointer.width / 2);
			context.lineTo(gaugeOption.pointer.width, 0);
			context.closePath();
			context.fill();
			context.beginPath();
			context.setFillStyle('#FFFFFF');
			context.arc(0, 0, gaugeOption.pointer.width / 6, 0, 2 * Math.PI, false);
			context.fill();
			context.restore();
		}

		if (opts.dataLabel !== false) {
			drawGaugeLabel(gaugeOption, radius, centerPosition, opts, config, context);
		}
	}

	//画仪表盘标题，副标题
	drawRingTitle(opts, config, context, centerPosition);

	if (process === 1 && opts.type === 'gauge') {
		opts.extra.gauge.oldAngle = series[0]._proportion_;
		opts.extra.gauge.oldData = series[0].data;
	}
	return {
		center: centerPosition,
		radius: radius,
		innerRadius: innerRadius,
		categories: categories,
		totalAngle: totalAngle
	};
}

function drawRadarDataPoints(series, opts, config, context) {
	var process = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 1;
	var radarOption = assign({}, {
		gridColor: '#cccccc',
		labelColor: '#666666',
		opacity: 0.2,
		gridCount: 3
	}, opts.extra.radar);

	var coordinateAngle = getRadarCoordinateSeries(opts.categories.length);

	var centerPosition = {
		x: opts.area[3] + (opts.width - opts.area[1] - opts.area[3]) / 2,
		y: opts.area[0] + (opts.height - opts.area[0] - opts.area[2]) / 2
	};

	var radius = Math.min(centerPosition.x - (getMaxTextListLength(opts.categories) + config.radarLabelTextMargin),
		centerPosition.y - config.radarLabelTextMargin);
	//TODO逻辑不对
	radius -= opts.padding[1];

	// draw grid
	context.beginPath();
	context.setLineWidth(1 * opts.pixelRatio);
	context.setStrokeStyle(radarOption.gridColor);
	coordinateAngle.forEach(function(angle) {
		var pos = convertCoordinateOrigin(radius * Math.cos(angle), radius * Math.sin(angle), centerPosition);
		context.moveTo(centerPosition.x, centerPosition.y);
		context.lineTo(pos.x, pos.y);
	});
	context.stroke();
	context.closePath();
	// draw split line grid

	var _loop = function _loop(i) {
		var startPos = {};
		context.beginPath();
		context.setLineWidth(1 * opts.pixelRatio);
		context.setStrokeStyle(radarOption.gridColor);
		coordinateAngle.forEach(function(angle, index) {
			var pos = convertCoordinateOrigin(radius / radarOption.gridCount * i * Math.cos(angle), radius /
				radarOption.gridCount * i * Math.sin(angle), centerPosition);
			if (index === 0) {
				startPos = pos;
				context.moveTo(pos.x, pos.y);
			} else {
				context.lineTo(pos.x, pos.y);
			}
		});
		context.lineTo(startPos.x, startPos.y);
		context.stroke();
		context.closePath();
	};

	for (var i = 1; i <= radarOption.gridCount; i++) {
		_loop(i);
	}

	var radarDataPoints = getRadarDataPoints(coordinateAngle, centerPosition, radius, series, opts, process);

	radarDataPoints.forEach(function(eachSeries, seriesIndex) {
		// 绘制区域数据
		context.beginPath();
		context.setFillStyle(hexToRgb(eachSeries.color, radarOption.opacity));
		eachSeries.data.forEach(function(item, index) {
			if (index === 0) {
				context.moveTo(item.position.x, item.position.y);
			} else {
				context.lineTo(item.position.x, item.position.y);
			}
		});
		context.closePath();
		context.fill();

		if (opts.dataPointShape !== false) {
			var points = eachSeries.data.map(function(item) {
				return item.position;
			});
			drawPointShape(points, eachSeries.color, eachSeries.pointShape, context, opts);
		}
	});
	// draw label text
	drawRadarLabel(coordinateAngle, radius, centerPosition, opts, config, context);

	return {
		center: centerPosition,
		radius: radius,
		angleList: coordinateAngle
	};
}

function normalInt(min, max, iter) {
	iter = iter == 0 ? 1 : iter;
	var arr = [];
	for (var i = 0; i < iter; i++) {
		arr[i] = Math.random();
	};
	return Math.floor(arr.reduce(function(i, j) {
		return i + j
	}) / iter * (max - min)) + min;
};

function collisionNew(area, points, width, height) {
	var isIn = false;
	for (let i = 0; i < points.length; i++) {
		if (points[i].area) {
			if (area[3] < points[i].area[1] || area[0] > points[i].area[2] || area[1] > points[i].area[3] || area[2] <
				points[i].area[0]) {
				if (area[0] < 0 || area[1] < 0 || area[2] > width || area[3] > height) {
					isIn = true;
					break;
				} else {
					isIn = false;
				}
			} else {
				isIn = true;
				break;
			}
		}
	}
	return isIn;
};

function getBoundingBox(data) {
	var bounds = {},
		coords;
	bounds.xMin = 180;
	bounds.xMax = 0;
	bounds.yMin = 90;
	bounds.yMax = 0
	for (var i = 0; i < data.length; i++) {
		var coorda = data[i].geometry.coordinates
		for (var k = 0; k < coorda.length; k++) {
			coords = coorda[k];
			if (coords.length == 1) {
				coords = coords[0]
			}
			for (var j = 0; j < coords.length; j++) {
				var longitude = coords[j][0];
				var latitude = coords[j][1];
				var point = {
					x: longitude,
					y: latitude
				}
				bounds.xMin = bounds.xMin < point.x ? bounds.xMin : point.x;
				bounds.xMax = bounds.xMax > point.x ? bounds.xMax : point.x;
				bounds.yMin = bounds.yMin < point.y ? bounds.yMin : point.y;
				bounds.yMax = bounds.yMax > point.y ? bounds.yMax : point.y;
			}
		}
	}
	return bounds;
}

function coordinateToPoint(latitude, longitude, bounds, scale, xoffset, yoffset) {
	return {
		x: (longitude - bounds.xMin) * scale + xoffset,
		y: (bounds.yMax - latitude) * scale + yoffset
	};
}

function pointToCoordinate(pointY, pointX, bounds, scale, xoffset, yoffset) {
	return {
		x: (pointX - xoffset) / scale + bounds.xMin,
		y: bounds.yMax - (pointY - yoffset) / scale
	};
}

function isRayIntersectsSegment(poi, s_poi, e_poi) {
	if (s_poi[1] == e_poi[1]) {
		return false;
	}
	if (s_poi[1] > poi[1] && e_poi[1] > poi[1]) {
		return false;
	}
	if (s_poi[1] < poi[1] && e_poi[1] < poi[1]) {
		return false;
	}
	if (s_poi[1] == poi[1] && e_poi[1] > poi[1]) {
		return false;
	}
	if (e_poi[1] == poi[1] && s_poi[1] > poi[1]) {
		return false;
	}
	if (s_poi[0] < poi[0] && e_poi[1] < poi[1]) {
		return false;
	}
	let xseg = e_poi[0] - (e_poi[0] - s_poi[0]) * (e_poi[1] - poi[1]) / (e_poi[1] - s_poi[1]);
	if (xseg < poi[0]) {
		return false;
	} else {
		return true;
	}
}

function isPoiWithinPoly(poi, poly) {
	let sinsc = 0;
	for (let i = 0; i < poly.length; i++) {
		let epoly = poly[i][0];
		if (poly.length == 1) {
			epoly = poly[i][0]
		}
		for (let j = 0; j < epoly.length - 1; j++) {
			let s_poi = epoly[j];
			let e_poi = epoly[j + 1];
			if (isRayIntersectsSegment(poi, s_poi, e_poi)) {
				sinsc += 1;
			}
		}
	}

	if (sinsc % 2 == 1) {
		return true;
	} else {
		return false;
	}
}


function drawMapDataPoints(series, opts, config, context) {
	var mapOption = assign({}, {
		border: true,
		borderWidth: 1,
		borderColor: '#666666',
		fillOpacity: 0.6,
		activeBorderColor: '#f04864',
		activeFillColor: '#facc14',
		activeFillOpacity: 1
	}, opts.extra.map);
	var coords, point;
	var data = series;
	var bounds = getBoundingBox(data);
	var xScale = opts.width / Math.abs(bounds.xMax - bounds.xMin);
	var yScale = opts.height / Math.abs(bounds.yMax - bounds.yMin);
	var scale = xScale < yScale ? xScale : yScale;
	var xoffset = opts.width / 2 - Math.abs(bounds.xMax - bounds.xMin) / 2 * scale;
	var yoffset = opts.height / 2 - Math.abs(bounds.yMax - bounds.yMin) / 2 * scale;
	context.beginPath();
	context.clearRect(0, 0, opts.width, opts.height);
	context.setFillStyle(opts.background || '#FFFFFF');
	context.rect(0, 0, opts.width, opts.height);
	context.fill();
	for (var i = 0; i < data.length; i++) {
		context.beginPath();
		context.setLineWidth(mapOption.borderWidth * opts.pixelRatio);
		context.setStrokeStyle(mapOption.borderColor);
		context.setFillStyle(hexToRgb(series[i].color, mapOption.fillOpacity));
		if (opts.tooltip) {
			if (opts.tooltip.index == i) {
				context.setStrokeStyle(mapOption.activeBorderColor);
				context.setFillStyle(hexToRgb(mapOption.activeFillColor, mapOption.activeFillOpacity));
			}
		}
		var coorda = data[i].geometry.coordinates
		for (var k = 0; k < coorda.length; k++) {
			coords = coorda[k];
			if (coords.length == 1) {
				coords = coords[0]
			}
			for (var j = 0; j < coords.length; j++) {
				point = coordinateToPoint(coords[j][1], coords[j][0], bounds, scale, xoffset, yoffset)
				if (j === 0) {
					context.beginPath();
					context.moveTo(point.x, point.y);
				} else {
					context.lineTo(point.x, point.y);
				}
			}
			context.fill();
			if (mapOption.border == true) {
				context.stroke();
			}
		}
		if (opts.dataLabel == true) {
			var centerPoint = data[i].properties.centroid;
			if (centerPoint) {
				point = coordinateToPoint(centerPoint[1], centerPoint[0], bounds, scale, xoffset, yoffset);
				let fontSize = data[i].textSize || config.fontSize;
				let text = data[i].properties.name;
				context.beginPath();
				context.setFontSize(fontSize)
				context.setFillStyle(data[i].textColor || '#666666')
				context.fillText(text, point.x - measureText(text, fontSize) / 2, point.y + fontSize / 2);
				context.closePath();
				context.stroke();
			}
		}
	}
	opts.chartData.mapData = {
		bounds: bounds,
		scale: scale,
		xoffset: xoffset,
		yoffset: yoffset
	}
	drawToolTipBridge(opts, config, context, 1);
	context.draw();
}

function getWordCloudPoint(opts, type) {
	let points = opts.series.sort(function(a, b) {
		return parseInt(b.textSize) - parseInt(a.textSize);
	});
	switch (type) {
		case 'normal':
			for (let i = 0; i < points.length; i++) {
				let text = points[i].name;
				let tHeight = points[i].textSize;
				let tWidth = measureText(text, tHeight);
				let x, y;
				let area;
				let breaknum = 0;
				while (true) {
					breaknum++;
					x = normalInt(-opts.width / 2, opts.width / 2, 5) - tWidth / 2;
					y = normalInt(-opts.height / 2, opts.height / 2, 5) + tHeight / 2;
					area = [x - 5 + opts.width / 2, y - 5 - tHeight + opts.height / 2, x + tWidth + 5 + opts.width / 2,
						y + 5 + opts.height / 2
					];
					let isCollision = collisionNew(area, points, opts.width, opts.height);
					if (!isCollision) break;
					if (breaknum == 1000) {
						area = [-100, -100, -100, -100];
						break;
					}
				};
				points[i].area = area;
			}
			break;
		case 'vertical':
			function Spin() {
				//获取均匀随机值，是否旋转，旋转的概率为（1-0.5）
				if (Math.random() > 0.7) {
					return true;
				} else {
					return false
				};
			};
			for (let i = 0; i < points.length; i++) {
				let text = points[i].name;
				let tHeight = points[i].textSize;
				let tWidth = measureText(text, tHeight);
				let isSpin = Spin();
				let x, y, area, areav;
				let breaknum = 0;
				while (true) {
					breaknum++;
					let isCollision;
					if (isSpin) {
						x = normalInt(-opts.width / 2, opts.width / 2, 5) - tWidth / 2;
						y = normalInt(-opts.height / 2, opts.height / 2, 5) + tHeight / 2;
						area = [y - 5 - tWidth + opts.width / 2, (-x - 5 + opts.height / 2), y + 5 + opts.width / 2, (-
							x + tHeight + 5 + opts.height / 2)];
						areav = [opts.width - (opts.width / 2 - opts.height / 2) - (-x + tHeight + 5 + opts.height /
							2) - 5, (opts.height / 2 - opts.width / 2) + (y - 5 - tWidth + opts.width / 2) - 5, opts
							.width - (opts.width / 2 - opts.height / 2) - (-x + tHeight + 5 + opts.height / 2) +
							tHeight, (opts.height / 2 - opts.width / 2) + (y - 5 - tWidth + opts.width / 2) +
							tWidth + 5
						];
						isCollision = collisionNew(areav, points, opts.height, opts.width);
					} else {
						x = normalInt(-opts.width / 2, opts.width / 2, 5) - tWidth / 2;
						y = normalInt(-opts.height / 2, opts.height / 2, 5) + tHeight / 2;
						area = [x - 5 + opts.width / 2, y - 5 - tHeight + opts.height / 2, x + tWidth + 5 + opts.width /
							2, y + 5 + opts.height / 2
						];
						isCollision = collisionNew(area, points, opts.width, opts.height);
					}
					if (!isCollision) break;
					if (breaknum == 1000) {
						area = [-1000, -1000, -1000, -1000];
						break;
					}
				};
				if (isSpin) {
					points[i].area = areav;
					points[i].areav = area;
				} else {
					points[i].area = area;
				}
				points[i].rotate = isSpin;
			};
			break;
	}
	return points;
}


function drawWordCloudDataPoints(series, opts, config, context) {
	let process = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 1;
	let wordOption = assign({}, {
		type: 'normal',
		autoColors: true
	}, opts.extra.word);

	context.beginPath();
	context.setFillStyle(opts.background || '#FFFFFF');
	context.rect(0, 0, opts.width, opts.height);
	context.fill();
	context.save();
	let points = opts.chartData.wordCloudData;
	context.translate(opts.width / 2, opts.height / 2);

	for (let i = 0; i < points.length; i++) {
		context.save();
		if (points[i].rotate) {
			context.rotate(90 * Math.PI / 180);
		}
		let text = points[i].name;
		let tHeight = points[i].textSize;
		let tWidth = measureText(text, tHeight);
		context.beginPath();
		context.setStrokeStyle(points[i].color);
		context.setFillStyle(points[i].color);
		context.setFontSize(tHeight);
		if (points[i].rotate) {
			if (points[i].areav[0] > 0) {
				if (opts.tooltip) {
					if (opts.tooltip.index == i) {
						context.strokeText(text, (points[i].areav[0] + 5 - opts.width / 2) * process - tWidth * (1 -
							process) / 2, (points[i].areav[1] + 5 + tHeight - opts.height / 2) * process);
					} else {
						context.fillText(text, (points[i].areav[0] + 5 - opts.width / 2) * process - tWidth * (1 -
							process) / 2, (points[i].areav[1] + 5 + tHeight - opts.height / 2) * process);
					}
				} else {
					context.fillText(text, (points[i].areav[0] + 5 - opts.width / 2) * process - tWidth * (1 -
						process) / 2, (points[i].areav[1] + 5 + tHeight - opts.height / 2) * process);
				}
			}
		} else {
			if (points[i].area[0] > 0) {
				if (opts.tooltip) {
					if (opts.tooltip.index == i) {
						context.strokeText(text, (points[i].area[0] + 5 - opts.width / 2) * process - tWidth * (1 -
							process) / 2, (points[i].area[1] + 5 + tHeight - opts.height / 2) * process);
					} else {
						context.fillText(text, (points[i].area[0] + 5 - opts.width / 2) * process - tWidth * (1 -
							process) / 2, (points[i].area[1] + 5 + tHeight - opts.height / 2) * process);
					}
				} else {
					context.fillText(text, (points[i].area[0] + 5 - opts.width / 2) * process - tWidth * (1 - process) /
						2, (points[i].area[1] + 5 + tHeight - opts.height / 2) * process);
				}

			}
		}

		context.stroke();
		context.restore();
	}
	context.restore();
}

function drawFunnelDataPoints(series, opts, config, context) {
	let process = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 1;
	let funnelOption = assign({}, {
		activeWidth: 10,
		activeOpacity: 0.3,
		border: false,
		borderWidth: 2,
		borderColor: '#FFFFFF',
		fillOpacity: 1,
		labelAlign: 'right'
	}, opts.extra.funnel);
	let eachSpacing = (opts.height - opts.area[0] - opts.area[2]) / series.length;
	let centerPosition = {
		x: opts.area[3] + (opts.width - opts.area[1] - opts.area[3]) / 2,
		y: opts.height - opts.area[2]
	};
	let activeWidth = funnelOption.activeWidth;
	let radius = Math.min((opts.width - opts.area[1] - opts.area[3]) / 2 - activeWidth, (opts.height - opts.area[0] -
		opts.area[2]) / 2 - activeWidth);
	series = getFunnelDataPoints(series, radius, process);
	context.save();
	context.translate(centerPosition.x, centerPosition.y);
	for (let i = 0; i < series.length; i++) {
		if (i == 0) {
			if (opts.tooltip) {
				if (opts.tooltip.index == i) {
					context.beginPath();
					context.setFillStyle(hexToRgb(series[i].color, funnelOption.activeOpacity));
					context.moveTo(-activeWidth, 0);
					context.lineTo(-series[i].radius - activeWidth, -eachSpacing);
					context.lineTo(series[i].radius + activeWidth, -eachSpacing);
					context.lineTo(activeWidth, 0);
					context.lineTo(-activeWidth, 0);
					context.closePath();
					context.fill();
				}
			}
			series[i].funnelArea = [centerPosition.x - series[i].radius, centerPosition.y - eachSpacing, centerPosition
				.x + series[i].radius, centerPosition.y
			];
			context.beginPath();
			context.setLineWidth(funnelOption.borderWidth * opts.pixelRatio);
			context.setStrokeStyle(funnelOption.borderColor);
			context.setFillStyle(hexToRgb(series[i].color, funnelOption.fillOpacity));
			context.moveTo(0, 0);
			context.lineTo(-series[i].radius, -eachSpacing);
			context.lineTo(series[i].radius, -eachSpacing);
			context.lineTo(0, 0);
			context.closePath();
			context.fill();
			if (funnelOption.border == true) {
				context.stroke();
			}
		} else {
			if (opts.tooltip) {
				if (opts.tooltip.index == i) {
					context.beginPath();
					context.setFillStyle(hexToRgb(series[i].color, funnelOption.activeOpacity));
					context.moveTo(0, 0);
					context.lineTo(-series[i - 1].radius - activeWidth, 0);
					context.lineTo(-series[i].radius - activeWidth, -eachSpacing);
					context.lineTo(series[i].radius + activeWidth, -eachSpacing);
					context.lineTo(series[i - 1].radius + activeWidth, 0);
					context.lineTo(0, 0);
					context.closePath();
					context.fill();
				}
			}
			series[i].funnelArea = [centerPosition.x - series[i].radius, centerPosition.y - eachSpacing * (i + 1),
				centerPosition.x + series[i].radius, centerPosition.y - eachSpacing * i
			];
			context.beginPath();
			context.setLineWidth(funnelOption.borderWidth * opts.pixelRatio);
			context.setStrokeStyle(funnelOption.borderColor);
			context.setFillStyle(hexToRgb(series[i].color, funnelOption.fillOpacity));
			context.moveTo(0, 0);
			context.lineTo(-series[i - 1].radius, 0);
			context.lineTo(-series[i].radius, -eachSpacing);
			context.lineTo(series[i].radius, -eachSpacing);
			context.lineTo(series[i - 1].radius, 0);
			context.lineTo(0, 0);
			context.closePath();
			context.fill();
			if (funnelOption.border == true) {
				context.stroke();
			}
		}
		context.translate(0, -eachSpacing)
	}
	context.restore();

	if (opts.dataLabel !== false && process === 1) {
		drawFunnelText(series, opts, context, eachSpacing, funnelOption.labelAlign, activeWidth, centerPosition);
	}

	return {
		center: centerPosition,
		radius: radius,
		series: series
	};
}

function drawFunnelText(series, opts, context, eachSpacing, labelAlign, activeWidth, centerPosition) {
	for (let i = 0; i < series.length; i++) {
		let item = series[i];
		let startX, endX, startY, fontSize;
		let text = item.format ? item.format(+item._proportion_.toFixed(2)) : util.toFixed(item._proportion_ * 100) +
			'%';
		if (labelAlign == 'right') {
			if (i == 0) {
				startX = (item.funnelArea[2] + centerPosition.x) / 2;
			} else {
				startX = (item.funnelArea[2] + series[i - 1].funnelArea[2]) / 2;
			}
			endX = startX + activeWidth * 2;
			startY = item.funnelArea[1] + eachSpacing / 2;
			fontSize = item.textSize || opts.fontSize;
			context.setLineWidth(1 * opts.pixelRatio);
			context.setStrokeStyle(item.color);
			context.setFillStyle(item.color);
			context.beginPath();
			context.moveTo(startX, startY);
			context.lineTo(endX, startY);
			context.stroke();
			context.closePath();
			context.beginPath();
			context.moveTo(endX, startY);
			context.arc(endX, startY, 2, 0, 2 * Math.PI);
			context.closePath();
			context.fill();
			context.beginPath();
			context.setFontSize(fontSize);
			context.setFillStyle(item.textColor || '#666666');
			context.fillText(text, endX + 5, startY + fontSize / 2 - 2);
			context.closePath();
			context.stroke();
			context.closePath();
		} else {
			if (i == 0) {
				startX = (item.funnelArea[0] + centerPosition.x) / 2;
			} else {
				startX = (item.funnelArea[0] + series[i - 1].funnelArea[0]) / 2;
			}
			endX = startX - activeWidth * 2;
			startY = item.funnelArea[1] + eachSpacing / 2;
			fontSize = item.textSize || opts.fontSize;
			context.setLineWidth(1 * opts.pixelRatio);
			context.setStrokeStyle(item.color);
			context.setFillStyle(item.color);
			context.beginPath();
			context.moveTo(startX, startY);
			context.lineTo(endX, startY);
			context.stroke();
			context.closePath();
			context.beginPath();
			context.moveTo(endX, startY);
			context.arc(endX, startY, 2, 0, 2 * Math.PI);
			context.closePath();
			context.fill();
			context.beginPath();
			context.setFontSize(fontSize);
			context.setFillStyle(item.textColor || '#666666');
			context.fillText(text, endX - 5 - measureText(text), startY + fontSize / 2 - 2);
			context.closePath();
			context.stroke();
			context.closePath();
		}

	}
}


function drawCanvas(opts, context) {
	context.draw();
}

var Timing = {
	easeIn: function easeIn(pos) {
		return Math.pow(pos, 3);
	},
	easeOut: function easeOut(pos) {
		return Math.pow(pos - 1, 3) + 1;
	},
	easeInOut: function easeInOut(pos) {
		if ((pos /= 0.5) < 1) {
			return 0.5 * Math.pow(pos, 3);
		} else {
			return 0.5 * (Math.pow(pos - 2, 3) + 2);
		}
	},
	linear: function linear(pos) {
		return pos;
	}
};

function Animation(opts) {
	this.isStop = false;
	opts.duration = typeof opts.duration === 'undefined' ? 1000 : opts.duration;
	opts.timing = opts.timing || 'linear';
	var delay = 17;

	function createAnimationFrame() {
		if (typeof setTimeout !== 'undefined') {
			return function(step, delay) {
				setTimeout(function() {
					var timeStamp = +new Date();
					step(timeStamp);
				}, delay);
			};
		} else if (typeof requestAnimationFrame !== 'undefined') {
			return requestAnimationFrame;
		} else {
			return function(step) {
				step(null);
			};
		}
	};
	var animationFrame = createAnimationFrame();
	var startTimeStamp = null;
	var _step = function step(timestamp) {
		if (timestamp === null || this.isStop === true) {
			opts.onProcess && opts.onProcess(1);
			opts.onAnimationFinish && opts.onAnimationFinish();
			return;
		}
		if (startTimeStamp === null) {
			startTimeStamp = timestamp;
		}
		if (timestamp - startTimeStamp < opts.duration) {
			var process = (timestamp - startTimeStamp) / opts.duration;
			var timingFunction = Timing[opts.timing];
			process = timingFunction(process);

			opts.onProcess && opts.onProcess(process);
			animationFrame(_step, delay);
		} else {
			opts.onProcess && opts.onProcess(1);
			opts.onAnimationFinish && opts.onAnimationFinish();
		}
	};
	_step = _step.bind(this);
	animationFrame(_step, delay);
}

// stop animation immediately
// and tigger onAnimationFinish
Animation.prototype.stop = function() {
	this.isStop = true;
};

function drawCharts(type, opts, config, context) {
	var _this = this;
	var series = opts.series;
	var categories = opts.categories;
	series = fillSeries(series, opts, config);
	var duration = opts.animation ? opts.duration : 0;
	_this.animationInstance && _this.animationInstance.stop();
	var seriesMA = null;
	if (type == 'candle') {
		let average = assign({}, opts.extra.candle.average);
		if (average.show) {
			seriesMA = calCandleMA(average.day, average.name, average.color, series[0].data);
			seriesMA = fillSeries(seriesMA, opts, config);
			opts.seriesMA = seriesMA;
		} else if (opts.seriesMA) {
			seriesMA = opts.seriesMA = fillSeries(opts.seriesMA, opts, config);
		} else {
			seriesMA = series;
		}
	} else {
		seriesMA = series;
	}

	/* 过滤掉show=false的series */
	opts._series_ = series = filterSeries(series);

	//重新计算图表区域

	opts.area = new Array(4);
	//复位绘图区域
	for (let j = 0; j < 4; j++) {
		opts.area[j] = opts.padding[j];
	}

	//通过计算三大区域：图例、X轴、Y轴的大小，确定绘图区域
	var _calLegendData = calLegendData(seriesMA, opts, config, opts.chartData),
		legendHeight = _calLegendData.area.wholeHeight,
		legendWidth = _calLegendData.area.wholeWidth;

	switch (opts.legend.position) {
		case 'top':
			opts.area[0] += legendHeight;
			break;
		case 'bottom':
			opts.area[2] += legendHeight;
			break;
		case 'left':
			opts.area[3] += legendWidth;
			break;
		case 'right':
			opts.area[1] += legendWidth;
			break;
	}

	let _calYAxisData = {},
		yAxisWidth = 0;
	if (opts.type === 'line' || opts.type === 'column' || opts.type === 'area' || opts.type === 'mix' || opts.type ===
		'candle') {
		_calYAxisData = calYAxisData(series, opts, config);
		yAxisWidth = _calYAxisData.yAxisWidth;
		//如果显示Y轴标题
		if (opts.yAxis.showTitle) {
			let maxTitleHeight = 0;
			for (let i = 0; i < opts.yAxis.data.length; i++) {
				maxTitleHeight = Math.max(maxTitleHeight, opts.yAxis.data[i].titleFontSize ? opts.yAxis.data[i]
					.titleFontSize : config.fontSize)
			}
			opts.area[0] += (maxTitleHeight + 6) * opts.pixelRatio;
		}
		let rightIndex = 0,
			leftIndex = 0;
		//计算主绘图区域左右位置
		for (let i = 0; i < yAxisWidth.length; i++) {
			if (yAxisWidth[i].position == 'left') {
				if (leftIndex > 0) {
					opts.area[3] += yAxisWidth[i].width + opts.yAxis.padding;
				} else {
					opts.area[3] += yAxisWidth[i].width;
				}
				leftIndex += 1;
			} else {
				if (rightIndex > 0) {
					opts.area[1] += yAxisWidth[i].width + opts.yAxis.padding;
				} else {
					opts.area[1] += yAxisWidth[i].width;
				}
				rightIndex += 1;
			}
		}
	} else {
		config.yAxisWidth = yAxisWidth;
	}
	opts.chartData.yAxisData = _calYAxisData;

	if (opts.categories && opts.categories.length) {
		opts.chartData.xAxisData = getXAxisPoints(opts.categories, opts, config);
		let _calCategoriesData = calCategoriesData(opts.categories, opts, config, opts.chartData.xAxisData.eachSpacing),
			xAxisHeight = _calCategoriesData.xAxisHeight,
			angle = _calCategoriesData.angle;
		config.xAxisHeight = xAxisHeight;
		config._xAxisTextAngle_ = angle;
		opts.area[2] += xAxisHeight;
		opts.chartData.categoriesData = _calCategoriesData;
	} else {
		if (opts.type === 'line' || opts.type === 'area' || opts.type === 'points') {
			opts.chartData.xAxisData = calXAxisData(series, opts, config);
			categories = opts.chartData.xAxisData.rangesFormat;
			let _calCategoriesData = calCategoriesData(categories, opts, config, opts.chartData.xAxisData.eachSpacing),
				xAxisHeight = _calCategoriesData.xAxisHeight,
				angle = _calCategoriesData.angle;
			config.xAxisHeight = xAxisHeight;
			config._xAxisTextAngle_ = angle;
			opts.area[2] += xAxisHeight;
			opts.chartData.categoriesData = _calCategoriesData;
		} else {
			opts.chartData.xAxisData = {
				xAxisPoints: []
			};
		}
	}
	//计算右对齐偏移距离
	if (opts.enableScroll && opts.xAxis.scrollAlign == 'right' && opts._scrollDistance_ === undefined) {
		let offsetLeft = 0,
			xAxisPoints = opts.chartData.xAxisData.xAxisPoints,
			startX = opts.chartData.xAxisData.startX,
			endX = opts.chartData.xAxisData.endX,
			eachSpacing = opts.chartData.xAxisData.eachSpacing;
		let totalWidth = eachSpacing * (xAxisPoints.length - 1);
		let screenWidth = endX - startX;
		offsetLeft = screenWidth - totalWidth;
		_this.scrollOption = {
			currentOffset: offsetLeft,
			startTouchX: offsetLeft,
			distance: 0,
			lastMoveTime: 0
		};
		opts._scrollDistance_ = offsetLeft;
	}

	if (type === 'pie' || type === 'ring' || type === 'rose') {
		config._pieTextMaxLength_ = opts.dataLabel === false ? 0 : getPieTextMaxLength(seriesMA);
	}

	switch (type) {
		case 'word':
			let wordOption = assign({}, {
				type: 'normal',
				autoColors: true
			}, opts.extra.word);
			if (opts.updateData == true || opts.updateData == undefined) {
				opts.chartData.wordCloudData = getWordCloudPoint(opts, wordOption.type);
			}
			this.animationInstance = new Animation({
				timing: 'easeInOut',
				duration: duration,
				onProcess: function(process) {
					context.clearRect(0, 0, opts.width, opts.height);
					if (opts.rotate) {
						contextRotate(context, opts);
					}
					drawWordCloudDataPoints(series, opts, config, context, process);
					drawCanvas(opts, context);
				},
				onAnimationFinish: function onAnimationFinish() {
					_this.event.trigger('renderComplete');
				}
			});
			break;
		case 'map':
			context.clearRect(0, 0, opts.width, opts.height);
			drawMapDataPoints(series, opts, config, context);
			break;
		case 'funnel':
			this.animationInstance = new Animation({
				timing: 'easeInOut',
				duration: duration,
				onProcess: function(process) {
					context.clearRect(0, 0, opts.width, opts.height);
					if (opts.rotate) {
						contextRotate(context, opts);
					}
					opts.chartData.funnelData = drawFunnelDataPoints(series, opts, config, context,
					process);
					drawLegend(opts.series, opts, config, context, opts.chartData);
					drawToolTipBridge(opts, config, context, process);
					drawCanvas(opts, context);
				},
				onAnimationFinish: function onAnimationFinish() {
					_this.event.trigger('renderComplete');
				}
			});
			break;
		case 'line':
			this.animationInstance = new Animation({
				timing: 'easeIn',
				duration: duration,
				onProcess: function onProcess(process) {
					context.clearRect(0, 0, opts.width, opts.height);
					if (opts.rotate) {
						contextRotate(context, opts);
					}
					drawYAxisGrid(categories, opts, config, context);
					drawXAxis(categories, opts, config, context);
					var _drawLineDataPoints = drawLineDataPoints(series, opts, config, context, process),
						xAxisPoints = _drawLineDataPoints.xAxisPoints,
						calPoints = _drawLineDataPoints.calPoints,
						eachSpacing = _drawLineDataPoints.eachSpacing;
					opts.chartData.xAxisPoints = xAxisPoints;
					opts.chartData.calPoints = calPoints;
					opts.chartData.eachSpacing = eachSpacing;
					drawYAxis(series, opts, config, context);
					if (opts.enableMarkLine !== false && process === 1) {
						drawMarkLine(opts, config, context);
					}
					drawLegend(opts.series, opts, config, context, opts.chartData);
					drawToolTipBridge(opts, config, context, process, eachSpacing, xAxisPoints);
					drawCanvas(opts, context);

				},
				onAnimationFinish: function onAnimationFinish() {
					_this.event.trigger('renderComplete');
				}
			});
			break;
		case 'mix':
			this.animationInstance = new Animation({
				timing: 'easeIn',
				duration: duration,
				onProcess: function onProcess(process) {
					context.clearRect(0, 0, opts.width, opts.height);
					if (opts.rotate) {
						contextRotate(context, opts);
					}
					drawYAxisGrid(categories, opts, config, context);
					drawXAxis(categories, opts, config, context);
					var _drawMixDataPoints = drawMixDataPoints(series, opts, config, context, process),
						xAxisPoints = _drawMixDataPoints.xAxisPoints,
						calPoints = _drawMixDataPoints.calPoints,
						eachSpacing = _drawMixDataPoints.eachSpacing;
					opts.chartData.xAxisPoints = xAxisPoints;
					opts.chartData.calPoints = calPoints;
					opts.chartData.eachSpacing = eachSpacing;
					drawYAxis(series, opts, config, context);
					if (opts.enableMarkLine !== false && process === 1) {
						drawMarkLine(opts, config, context);
					}
					drawLegend(opts.series, opts, config, context, opts.chartData);
					drawToolTipBridge(opts, config, context, process, eachSpacing, xAxisPoints);
					drawCanvas(opts, context);
				},
				onAnimationFinish: function onAnimationFinish() {
					_this.event.trigger('renderComplete');
				}
			});
			break;
		case 'column':
			this.animationInstance = new Animation({
				timing: 'easeIn',
				duration: duration,
				onProcess: function onProcess(process) {
					context.clearRect(0, 0, opts.width, opts.height);
					if (opts.rotate) {
						contextRotate(context, opts);
					}
					drawYAxisGrid(categories, opts, config, context);
					drawXAxis(categories, opts, config, context);
					var _drawColumnDataPoints = drawColumnDataPoints(series, opts, config, context,
						process),
						xAxisPoints = _drawColumnDataPoints.xAxisPoints,
						calPoints = _drawColumnDataPoints.calPoints,
						eachSpacing = _drawColumnDataPoints.eachSpacing;
					opts.chartData.xAxisPoints = xAxisPoints;
					opts.chartData.calPoints = calPoints;
					opts.chartData.eachSpacing = eachSpacing;
					drawYAxis(series, opts, config, context);
					if (opts.enableMarkLine !== false && process === 1) {
						drawMarkLine(opts, config, context);
					}
					drawLegend(opts.series, opts, config, context, opts.chartData);
					drawToolTipBridge(opts, config, context, process, eachSpacing, xAxisPoints);
					drawCanvas(opts, context);
				},
				onAnimationFinish: function onAnimationFinish() {
					_this.event.trigger('renderComplete');
				}
			});
			break;
		case 'area':
			this.animationInstance = new Animation({
				timing: 'easeIn',
				duration: duration,
				onProcess: function onProcess(process) {
					context.clearRect(0, 0, opts.width, opts.height);
					if (opts.rotate) {
						contextRotate(context, opts);
					}
					drawYAxisGrid(categories, opts, config, context);
					drawXAxis(categories, opts, config, context);
					var _drawAreaDataPoints = drawAreaDataPoints(series, opts, config, context, process),
						xAxisPoints = _drawAreaDataPoints.xAxisPoints,
						calPoints = _drawAreaDataPoints.calPoints,
						eachSpacing = _drawAreaDataPoints.eachSpacing;
					opts.chartData.xAxisPoints = xAxisPoints;
					opts.chartData.calPoints = calPoints;
					opts.chartData.eachSpacing = eachSpacing;
					drawYAxis(series, opts, config, context);
					if (opts.enableMarkLine !== false && process === 1) {
						drawMarkLine(opts, config, context);
					}
					drawLegend(opts.series, opts, config, context, opts.chartData);
					drawToolTipBridge(opts, config, context, process, eachSpacing, xAxisPoints);
					drawCanvas(opts, context);
				},
				onAnimationFinish: function onAnimationFinish() {
					_this.event.trigger('renderComplete');
				}
			});
			break;
		case 'ring':
		case 'pie':
			this.animationInstance = new Animation({
				timing: 'easeInOut',
				duration: duration,
				onProcess: function onProcess(process) {
					context.clearRect(0, 0, opts.width, opts.height);
					if (opts.rotate) {
						contextRotate(context, opts);
					}
					opts.chartData.pieData = drawPieDataPoints(series, opts, config, context, process);
					drawLegend(opts.series, opts, config, context, opts.chartData);
					drawToolTipBridge(opts, config, context, process);
					drawCanvas(opts, context);
				},
				onAnimationFinish: function onAnimationFinish() {
					_this.event.trigger('renderComplete');
				}
			});
			break;
		case 'rose':
			this.animationInstance = new Animation({
				timing: 'easeInOut',
				duration: duration,
				onProcess: function onProcess(process) {
					context.clearRect(0, 0, opts.width, opts.height);
					if (opts.rotate) {
						contextRotate(context, opts);
					}
					opts.chartData.pieData = drawRoseDataPoints(series, opts, config, context, process);
					drawLegend(opts.series, opts, config, context, opts.chartData);
					drawToolTipBridge(opts, config, context, process);
					drawCanvas(opts, context);
				},
				onAnimationFinish: function onAnimationFinish() {
					_this.event.trigger('renderComplete');
				}
			});
			break;
		case 'radar':
			this.animationInstance = new Animation({
				timing: 'easeInOut',
				duration: duration,
				onProcess: function onProcess(process) {
					context.clearRect(0, 0, opts.width, opts.height);
					if (opts.rotate) {
						contextRotate(context, opts);
					}
					opts.chartData.radarData = drawRadarDataPoints(series, opts, config, context, process);
					drawLegend(opts.series, opts, config, context, opts.chartData);
					drawToolTipBridge(opts, config, context, process);
					drawCanvas(opts, context);
				},
				onAnimationFinish: function onAnimationFinish() {
					_this.event.trigger('renderComplete');
				}
			});
			break;
		case 'arcbar':
			this.animationInstance = new Animation({
				timing: 'easeInOut',
				duration: duration,
				onProcess: function onProcess(process) {
					context.clearRect(0, 0, opts.width, opts.height);
					if (opts.rotate) {
						contextRotate(context, opts);
					}
					opts.chartData.arcbarData = drawArcbarDataPoints(series, opts, config, context,
					process);
					drawCanvas(opts, context);
				},
				onAnimationFinish: function onAnimationFinish() {
					_this.event.trigger('renderComplete');
				}
			});
			break;
		case 'gauge':
			this.animationInstance = new Animation({
				timing: 'easeInOut',
				duration: duration,
				onProcess: function onProcess(process) {
					context.clearRect(0, 0, opts.width, opts.height);
					if (opts.rotate) {
						contextRotate(context, opts);
					}
					opts.chartData.gaugeData = drawGaugeDataPoints(categories, series, opts, config,
						context, process);
					drawCanvas(opts, context);
				},
				onAnimationFinish: function onAnimationFinish() {
					_this.event.trigger('renderComplete');
				}
			});
			break;
		case 'candle':
			this.animationInstance = new Animation({
				timing: 'easeIn',
				duration: duration,
				onProcess: function onProcess(process) {
					context.clearRect(0, 0, opts.width, opts.height);
					if (opts.rotate) {
						contextRotate(context, opts);
					}
					drawYAxisGrid(categories, opts, config, context);
					drawXAxis(categories, opts, config, context);
					var _drawCandleDataPoints = drawCandleDataPoints(series, seriesMA, opts, config,
							context, process),
						xAxisPoints = _drawCandleDataPoints.xAxisPoints,
						calPoints = _drawCandleDataPoints.calPoints,
						eachSpacing = _drawCandleDataPoints.eachSpacing;
					opts.chartData.xAxisPoints = xAxisPoints;
					opts.chartData.calPoints = calPoints;
					opts.chartData.eachSpacing = eachSpacing;
					drawYAxis(series, opts, config, context);
					if (opts.enableMarkLine !== false && process === 1) {
						drawMarkLine(opts, config, context);
					}
					if (seriesMA) {
						drawLegend(seriesMA, opts, config, context, opts.chartData);
					} else {
						drawLegend(opts.series, opts, config, context, opts.chartData);
					}
					drawToolTipBridge(opts, config, context, process, eachSpacing, xAxisPoints);
					drawCanvas(opts, context);
				},
				onAnimationFinish: function onAnimationFinish() {
					_this.event.trigger('renderComplete');
				}
			});
			break;
	}
}

// simple event implement

function Event() {
	this.events = {};
}

Event.prototype.addEventListener = function(type, listener) {
	this.events[type] = this.events[type] || [];
	this.events[type].push(listener);
};

Event.prototype.trigger = function() {
	for (var _len = arguments.length, args = Array(_len), _key = 0; _key < _len; _key++) {
		args[_key] = arguments[_key];
	}

	var type = args[0];
	var params = args.slice(1);
	if (!!this.events[type]) {
		this.events[type].forEach(function(listener) {
			try {
				listener.apply(null, params);
			} catch (e) {}
		});
	}
};

var Charts = function Charts(opts) {
	opts.pixelRatio = opts.pixelRatio ? opts.pixelRatio : 1;
	opts.fontSize = opts.fontSize ? opts.fontSize * opts.pixelRatio : 13 * opts.pixelRatio;
	opts.title = assign({}, opts.title);
	opts.subtitle = assign({}, opts.subtitle);
	opts.duration = opts.duration ? opts.duration : 1000;
	opts.yAxis = assign({}, {
		data: [],
		showTitle: false,
		disabled: false,
		disableGrid: false,
		splitNumber: 5,
		gridType: 'solid',
		dashLength: 4 * opts.pixelRatio,
		gridColor: '#cccccc',
		padding: 10,
		fontColor: '#666666'
	}, opts.yAxis);
	opts.yAxis.dashLength *= opts.pixelRatio;
	opts.yAxis.padding *= opts.pixelRatio;
	opts.xAxis = assign({}, {
		rotateLabel: false,
		type: 'calibration',
		gridType: 'solid',
		dashLength: 4,
		scrollAlign: 'left',
		boundaryGap: 'center',
		axisLine: true,
		axisLineColor: '#cccccc'
	}, opts.xAxis);
	opts.xAxis.dashLength *= opts.pixelRatio;
	opts.legend = assign({}, {
		show: true,
		position: 'bottom',
		float: 'center',
		backgroundColor: 'rgba(0,0,0,0)',
		borderColor: 'rgba(0,0,0,0)',
		borderWidth: 0,
		padding: 5,
		margin: 5,
		itemGap: 10,
		fontSize: opts.fontSize,
		lineHeight: opts.fontSize,
		fontColor: '#333333',
		format: {},
		hiddenColor: '#CECECE'
	}, opts.legend);
	opts.legend.borderWidth = opts.legend.borderWidth * opts.pixelRatio;
	opts.legend.itemGap = opts.legend.itemGap * opts.pixelRatio;
	opts.legend.padding = opts.legend.padding * opts.pixelRatio;
	opts.legend.margin = opts.legend.margin * opts.pixelRatio;
	opts.extra = assign({}, opts.extra);
	opts.rotate = opts.rotate ? true : false;
	opts.animation = opts.animation ? true : false;
	opts.rotate = opts.rotate ? true : false;

	let config$$1 = JSON.parse(JSON.stringify(config));
	config$$1.colors = opts.colors ? opts.colors : config$$1.colors;
	config$$1.yAxisTitleWidth = opts.yAxis.disabled !== true && opts.yAxis.title ? config$$1.yAxisTitleWidth : 0;
	if (opts.type == 'pie' || opts.type == 'ring') {
		config$$1.pieChartLinePadding = opts.dataLabel === false ? 0 : opts.extra.pie.labelWidth * opts
			.pixelRatio || config$$1.pieChartLinePadding * opts.pixelRatio;
	}
	if (opts.type == 'rose') {
		config$$1.pieChartLinePadding = opts.dataLabel === false ? 0 : opts.extra.rose.labelWidth * opts
			.pixelRatio || config$$1.pieChartLinePadding * opts.pixelRatio;
	}
	config$$1.pieChartTextPadding = opts.dataLabel === false ? 0 : config$$1.pieChartTextPadding * opts.pixelRatio;
	config$$1.yAxisSplit = opts.yAxis.splitNumber ? opts.yAxis.splitNumber : config.yAxisSplit;

	//屏幕旋转
	config$$1.rotate = opts.rotate;
	if (opts.rotate) {
		let tempWidth = opts.width;
		let tempHeight = opts.height;
		opts.width = tempHeight;
		opts.height = tempWidth;
	}

	//适配高分屏
	opts.padding = opts.padding ? opts.padding : config$$1.padding;
	for (let i = 0; i < 4; i++) {
		opts.padding[i] *= opts.pixelRatio;
	}
	config$$1.yAxisWidth = config.yAxisWidth * opts.pixelRatio;
	config$$1.xAxisHeight = config.xAxisHeight * opts.pixelRatio;
	if (opts.enableScroll && opts.xAxis.scrollShow) {
		config$$1.xAxisHeight += 6 * opts.pixelRatio;
	}
	config$$1.xAxisLineHeight = config.xAxisLineHeight * opts.pixelRatio;
	config$$1.fontSize = opts.fontSize;
	config$$1.titleFontSize = config.titleFontSize * opts.pixelRatio;
	config$$1.subtitleFontSize = config.subtitleFontSize * opts.pixelRatio;
	config$$1.toolTipPadding = config.toolTipPadding * opts.pixelRatio;
	config$$1.toolTipLineHeight = config.toolTipLineHeight * opts.pixelRatio;
	config$$1.columePadding = config.columePadding * opts.pixelRatio;
	opts.$this = opts.$this ? opts.$this : this;

	this.context = uni.createCanvasContext(opts.canvasId, opts.$this);
	/* 兼容原生H5
	this.context = document.getElementById(opts.canvasId).getContext("2d");
	this.context.setStrokeStyle = function(e){ return this.strokeStyle=e; }
	this.context.setLineWidth = function(e){ return this.lineWidth=e; }
	this.context.setLineCap = function(e){ return this.lineCap=e; }
	this.context.setFontSize = function(e){ return this.font=e+"px sans-serif"; }
	this.context.setFillStyle = function(e){ return this.fillStyle=e; }
	this.context.draw = function(){ }
	*/

	opts.chartData = {};
	this.event = new Event();
	this.scrollOption = {
		currentOffset: 0,
		startTouchX: 0,
		distance: 0,
		lastMoveTime: 0
	};

	this.opts = opts;
	this.config = config$$1;

	drawCharts.call(this, opts.type, opts, config$$1, this.context);
};

Charts.prototype.updateData = function() {
	let data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	this.opts = assign({}, this.opts, data);
	this.opts.updateData = true;
	let scrollPosition = data.scrollPosition || 'current';
	switch (scrollPosition) {
		case 'current':
			this.opts._scrollDistance_ = this.scrollOption.currentOffset;
			break;
		case 'left':
			this.opts._scrollDistance_ = 0;
			this.scrollOption = {
				currentOffset: 0,
				startTouchX: 0,
				distance: 0,
				lastMoveTime: 0
			};
			break;
		case 'right':
			let _calYAxisData = calYAxisData(this.opts.series, this.opts, this.config),
				yAxisWidth = _calYAxisData.yAxisWidth;
			this.config.yAxisWidth = yAxisWidth;
			let offsetLeft = 0;
			let _getXAxisPoints0 = getXAxisPoints(this.opts.categories, this.opts, this.config),
				xAxisPoints = _getXAxisPoints0.xAxisPoints,
				startX = _getXAxisPoints0.startX,
				endX = _getXAxisPoints0.endX,
				eachSpacing = _getXAxisPoints0.eachSpacing;
			let totalWidth = eachSpacing * (xAxisPoints.length - 1);
			let screenWidth = endX - startX;
			offsetLeft = screenWidth - totalWidth;
			this.scrollOption = {
				currentOffset: offsetLeft,
				startTouchX: offsetLeft,
				distance: 0,
				lastMoveTime: 0
			};
			this.opts._scrollDistance_ = offsetLeft;
			break;
	}
	drawCharts.call(this, this.opts.type, this.opts, this.config, this.context);
};

Charts.prototype.zoom = function() {
	var val = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : this.opts.xAxis.itemCount;
	if (this.opts.enableScroll !== true) {
		console.log('请启用滚动条后使用！')
		return;
	}
	//当前屏幕中间点
	let centerPoint = Math.round(Math.abs(this.scrollOption.currentOffset) / this.opts.chartData.eachSpacing) + Math
		.round(
			this.opts.xAxis.itemCount / 2);
	this.opts.animation = false;
	this.opts.xAxis.itemCount = val.itemCount;
	//重新计算x轴偏移距离
	let _calYAxisData = calYAxisData(this.opts.series, this.opts, this.config),
		yAxisWidth = _calYAxisData.yAxisWidth;
	this.config.yAxisWidth = yAxisWidth;
	let offsetLeft = 0;
	let _getXAxisPoints0 = getXAxisPoints(this.opts.categories, this.opts, this.config),
		xAxisPoints = _getXAxisPoints0.xAxisPoints,
		startX = _getXAxisPoints0.startX,
		endX = _getXAxisPoints0.endX,
		eachSpacing = _getXAxisPoints0.eachSpacing;
	let centerLeft = eachSpacing * centerPoint;
	let screenWidth = endX - startX;
	let MaxLeft = screenWidth - eachSpacing * (xAxisPoints.length - 1);
	offsetLeft = screenWidth / 2 - centerLeft;
	if (offsetLeft > 0) {
		offsetLeft = 0;
	}
	if (offsetLeft < MaxLeft) {
		offsetLeft = MaxLeft;
	}
	this.scrollOption = {
		currentOffset: offsetLeft,
		startTouchX: offsetLeft,
		distance: 0,
		lastMoveTime: 0
	};
	this.opts._scrollDistance_ = offsetLeft;
	drawCharts.call(this, this.opts.type, this.opts, this.config, this.context);
};

Charts.prototype.stopAnimation = function() {
	this.animationInstance && this.animationInstance.stop();
};

Charts.prototype.addEventListener = function(type, listener) {
	this.event.addEventListener(type, listener);
};

Charts.prototype.getCurrentDataIndex = function(e) {
	var touches = null;
	if (e.changedTouches) {
		touches = e.changedTouches[0];
	} else {
		touches = e.mp.changedTouches[0];
	}
	if (touches) {
		let _touches$ = getTouches(touches, this.opts, e);
		if (this.opts.type === 'pie' || this.opts.type === 'ring' || this.opts.type === 'rose') {
			return findPieChartCurrentIndex({
				x: _touches$.x,
				y: _touches$.y
			}, this.opts.chartData.pieData);
		} else if (this.opts.type === 'radar') {
			return findRadarChartCurrentIndex({
				x: _touches$.x,
				y: _touches$.y
			}, this.opts.chartData.radarData, this.opts.categories.length);
		} else if (this.opts.type === 'funnel') {
			return findFunnelChartCurrentIndex({
				x: _touches$.x,
				y: _touches$.y
			}, this.opts.chartData.funnelData);
		} else if (this.opts.type === 'map') {
			return findMapChartCurrentIndex({
				x: _touches$.x,
				y: _touches$.y
			}, this.opts);
		} else if (this.opts.type === 'word') {
			return findWordChartCurrentIndex({
				x: _touches$.x,
				y: _touches$.y
			}, this.opts.chartData.wordCloudData);
		} else {
			return findCurrentIndex({
				x: _touches$.x,
				y: _touches$.y
			}, this.opts.chartData.calPoints, this.opts, this.config, Math.abs(this.scrollOption
				.currentOffset));
		}
	}
	return -1;
};

Charts.prototype.getLegendDataIndex = function(e) {
	var touches = null;
	if (e.changedTouches) {
		touches = e.changedTouches[0];
	} else {
		touches = e.mp.changedTouches[0];
	}
	if (touches) {
		let _touches$ = getTouches(touches, this.opts, e);
		return findLegendIndex({
			x: _touches$.x,
			y: _touches$.y
		}, this.opts.chartData.legendData);
	}
	return -1;
};

Charts.prototype.touchLegend = function(e) {
	var option = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	var touches = null;
	if (e.changedTouches) {
		touches = e.changedTouches[0];
	} else {
		touches = e.mp.changedTouches[0];
	}
	if (touches) {
		var _touches$ = getTouches(touches, this.opts, e);
		var index = this.getLegendDataIndex(e);
		if (index >= 0) {
			this.opts.series[index].show = !this.opts.series[index].show;
			this.opts.animation = option.animation ? true : false;
			this.opts._scrollDistance_ = this.scrollOption.currentOffset;
			drawCharts.call(this, this.opts.type, this.opts, this.config, this.context);
		}
	}

};

Charts.prototype.showToolTip = function(e) {
	var option = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	var touches = null;
	if (e.changedTouches) {
		touches = e.changedTouches[0];
	} else {
		touches = e.mp.changedTouches[0];
	}
	if (!touches) {}
	var _touches$ = getTouches(touches, this.opts, e);
	var currentOffset = this.scrollOption.currentOffset;
	var opts = assign({}, this.opts, {
		_scrollDistance_: currentOffset,
		animation: false
	});
	if (this.opts.type === 'line' || this.opts.type === 'area' || this.opts.type === 'column') {
		var index = option.index == undefined ? this.getCurrentDataIndex(e) : option.index;
		if (index > -1) {
			var seriesData = getSeriesDataItem(this.opts.series, index);
			if (seriesData.length !== 0) {
				var _getToolTipData = getToolTipData(seriesData, this.opts.chartData.calPoints, index, this.opts
						.categories, option),
					textList = _getToolTipData.textList,
					offset = _getToolTipData.offset;
				offset.y = _touches$.y;
				opts.tooltip = {
					textList: option.textList ? option.textList : textList,
					offset: offset,
					option: option,
					index: index
				};
			}
		}
		drawCharts.call(this, opts.type, opts, this.config, this.context);
	}
	if (this.opts.type === 'mix') {
		var index = option.index == undefined ? this.getCurrentDataIndex(e) : option.index;
		if (index > -1) {
			var currentOffset = this.scrollOption.currentOffset;
			var opts = assign({}, this.opts, {
				_scrollDistance_: currentOffset,
				animation: false
			});
			var seriesData = getSeriesDataItem(this.opts.series, index);
			if (seriesData.length !== 0) {
				var _getMixToolTipData = getMixToolTipData(seriesData, this.opts.chartData.calPoints, index, this
						.opts.categories, option),
					textList = _getMixToolTipData.textList,
					offset = _getMixToolTipData.offset;
				offset.y = _touches$.y;
				opts.tooltip = {
					textList: option.textList ? option.textList : textList,
					offset: offset,
					option: option,
					index: index
				};
			}
		}
		drawCharts.call(this, opts.type, opts, this.config, this.context);
	}
	if (this.opts.type === 'candle') {
		var index = option.index == undefined ? this.getCurrentDataIndex(e) : option.index;
		if (index > -1) {
			var currentOffset = this.scrollOption.currentOffset;
			var opts = assign({}, this.opts, {
				_scrollDistance_: currentOffset,
				animation: false
			});
			var seriesData = getSeriesDataItem(this.opts.series, index);
			if (seriesData.length !== 0) {
				var _getToolTipData = getCandleToolTipData(this.opts.series[0].data, seriesData, this.opts.chartData
						.calPoints,
						index, this.opts.categories, this.opts.extra.candle, option),
					textList = _getToolTipData.textList,
					offset = _getToolTipData.offset;
				offset.y = _touches$.y;
				opts.tooltip = {
					textList: option.textList ? option.textList : textList,
					offset: offset,
					option: option,
					index: index
				};
			}
		}
		drawCharts.call(this, opts.type, opts, this.config, this.context);
	}
	if (this.opts.type === 'pie' || this.opts.type === 'ring' || this.opts.type === 'rose' || this.opts.type ===
		'funnel') {
		var index = option.index == undefined ? this.getCurrentDataIndex(e) : option.index;
		if (index > -1) {
			var currentOffset = this.scrollOption.currentOffset;
			var opts = assign({}, this.opts, {
				_scrollDistance_: currentOffset,
				animation: false
			});
			var seriesData = this.opts._series_[index];
			var textList = [{
				text: option.format ? option.format(seriesData) : seriesData.name + ': ' + seriesData.data,
				color: seriesData.color
			}];
			var offset = {
				x: _touches$.x,
				y: _touches$.y
			};
			opts.tooltip = {
				textList: option.textList ? option.textList : textList,
				offset: offset,
				option: option,
				index: index
			};
		}
		drawCharts.call(this, opts.type, opts, this.config, this.context);
	}
	if (this.opts.type === 'map' || this.opts.type === 'word') {
		var index = option.index == undefined ? this.getCurrentDataIndex(e) : option.index;
		if (index > -1) {
			var currentOffset = this.scrollOption.currentOffset;
			var opts = assign({}, this.opts, {
				_scrollDistance_: currentOffset,
				animation: false
			});
			var seriesData = this.opts._series_[index];
			var textList = [{
				text: option.format ? option.format(seriesData) : seriesData.properties.name,
				color: seriesData.color
			}];
			var offset = {
				x: _touches$.x,
				y: _touches$.y
			};
			opts.tooltip = {
				textList: option.textList ? option.textList : textList,
				offset: offset,
				option: option,
				index: index
			};
		}
		opts.updateData = false;
		drawCharts.call(this, opts.type, opts, this.config, this.context);
	}
	if (this.opts.type === 'radar') {
		var index = option.index == undefined ? this.getCurrentDataIndex(e) : option.index;
		if (index > -1) {
			var currentOffset = this.scrollOption.currentOffset;
			var opts = assign({}, this.opts, {
				_scrollDistance_: currentOffset,
				animation: false
			});
			var seriesData = getSeriesDataItem(this.opts.series, index);
			if (seriesData.length !== 0) {
				var textList = seriesData.map(function(item) {
					return {
						text: option.format ? option.format(item) : item.name + ': ' + item.data,
						color: item.color
					};
				});
				var offset = {
					x: _touches$.x,
					y: _touches$.y
				};
				opts.tooltip = {
					textList: option.textList ? option.textList : textList,
					offset: offset,
					option: option,
					index: index
				};
			}
		}
		drawCharts.call(this, opts.type, opts, this.config, this.context);
	}
};

Charts.prototype.translate = function(distance) {
	this.scrollOption = {
		currentOffset: distance,
		startTouchX: distance,
		distance: 0,
		lastMoveTime: 0
	};
	let opts = assign({}, this.opts, {
		_scrollDistance_: distance,
		animation: false
	});
	drawCharts.call(this, this.opts.type, opts, this.config, this.context);
};

Charts.prototype.scrollStart = function(e) {
	var touches = null;
	if (e.changedTouches) {
		touches = e.changedTouches[0];
	} else {
		touches = e.mp.changedTouches[0];
	}
	var _touches$ = getTouches(touches, this.opts, e);
	if (touches && this.opts.enableScroll === true) {
		this.scrollOption.startTouchX = _touches$.x;
	}
};

Charts.prototype.scroll = function(e) {
	if (this.scrollOption.lastMoveTime === 0) {
		this.scrollOption.lastMoveTime = Date.now();
	}
	let Limit = this.opts.extra.touchMoveLimit || 20;
	let currMoveTime = Date.now();
	let duration = currMoveTime - this.scrollOption.lastMoveTime;
	if (duration < Math.floor(1000 / Limit)) return;
	this.scrollOption.lastMoveTime = currMoveTime;
	var touches = null;
	if (e.changedTouches) {
		touches = e.changedTouches[0];
	} else {
		touches = e.mp.changedTouches[0];
	}
	if (touches && this.opts.enableScroll === true) {
		var _touches$ = getTouches(touches, this.opts, e);
		var _distance;
		_distance = _touches$.x - this.scrollOption.startTouchX;
		var currentOffset = this.scrollOption.currentOffset;
		var validDistance = calValidDistance(this, currentOffset + _distance, this.opts.chartData, this.config, this
			.opts);
		this.scrollOption.distance = _distance = validDistance - currentOffset;
		var opts = assign({}, this.opts, {
			_scrollDistance_: currentOffset + _distance,
			animation: false
		});
		drawCharts.call(this, opts.type, opts, this.config, this.context);
		return currentOffset + _distance;
	}
};

Charts.prototype.scrollEnd = function(e) {
	if (this.opts.enableScroll === true) {
		var _scrollOption = this.scrollOption,
			currentOffset = _scrollOption.currentOffset,
			distance = _scrollOption.distance;
		this.scrollOption.currentOffset = currentOffset + distance;
		this.scrollOption.distance = 0;
	}
};
if (typeof module === "object" && typeof module.exports === "object") {
	module.exports = Charts;
	//export default Charts;//建议使用nodejs的module导出方式，如报错请使用export方式导出
}
