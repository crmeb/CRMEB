const forMatNum=(num)=>{
	return num<10?'0'+num:num+'';
}
const initPicker={
	//日期
	date:{
		init(start,end,mode="date",step,value,flag,disabled,hasSecond){
			let aToday=new Date();
			let tYear,tMonth,tDay,tHours,tMinutes,tSeconds,defaultVal=[];
			let initstartDate=new Date(start.toString());
			let endDate=new Date(end.toString());
			if(start>end){
				initstartDate=new Date(end.toString());
				endDate=new Date(start.toString());
			};
			let startYear=initstartDate.getFullYear();
			let startMonth=initstartDate.getMonth()+1;
			let endYear=endDate.getFullYear();
			let years=[],months=[],days=[],hours=[],minutes=[],seconds=[],areas=[],returnArr=[],dvalDate=[];
			switch(mode){
				case "half":
					dvalDate=flag?[...value.split(" ")[0].split("-"),...value.split(" ")[1].split(":")]:[...value.split(" ")[0].split("-"),value.split(" ")[1]];
					break;
				case "date":
				case "yearMonth":
					dvalDate=value.split("-");
					break;
				case "dateTime":
					dvalDate=[...value.split(" ")[0].split("-"),...value.split(" ")[1].split(":")];
					break;
				case "time":
					dvalDate=value.split(":");
					break;
			}
			let curMonth=flag?dvalDate[1]*1:(dvalDate[1]+1);
			let dYear=aToday.getFullYear();
			let dMonth=aToday.getMonth()+1;
			let dDate=aToday.getDate();
			let totalDays=new Date(startYear,curMonth,0).getDate();
			let dvalObj={};
			switch(mode){
				case "half":
				case "date":
				case "yearMonth":
					let curYear=dvalDate[0];
					let curMonth=dvalDate[1];
					if(disabled){
						for(let s=startYear;s<=dYear;s++){
							years.push(s+'');
						};
						if(curYear==dYear){
							for(let m=1;m<=dMonth;m++){
								months.push(forMatNum(m));
							};
						}else{
							for(let m=1;m<=12;m++){
								months.push(forMatNum(m));
							};
						}
						if(curMonth==dMonth){
							for(let d=1;d<=dDate;d++){
								days.push(forMatNum(d));
							}
						}else{
							for(let d=1;d<=totalDays;d++){
								days.push(forMatNum(d));
							}
						}
						
					}else{
						for(let s=startYear;s<=endYear;s++){
							years.push(s+'');
						};
						for(let m=1;m<=12;m++){
							months.push(forMatNum(m));
						};
						for(let d=1;d<=totalDays;d++){
							days.push(forMatNum(d));
						}
					};
					break;
				default:
					for(let s=startYear;s<=endYear;s++){
						years.push(s+'');
					};
					for(let m=1;m<=12;m++){
						months.push(forMatNum(m));
					};
					for(let d=1;d<=totalDays;d++){
						days.push(forMatNum(d));
					}
					break;
			}
			for(let h=0;h<24;h++){
				hours.push(forMatNum(h));
			}
			for(let m=0;m<60;m+=step*1){
				minutes.push(forMatNum(m));
			}
			for(let s=0;s<60;s++){
				seconds.push(forMatNum(s));
			}
			if(flag){
				returnArr=[
					years.indexOf(dvalDate[0]),
					months.indexOf(dvalDate[1]),
					days.indexOf(dvalDate[2]),
					hours.indexOf(dvalDate[3]),
					minutes.indexOf(dvalDate[4])==-1?0:minutes.indexOf(dvalDate[4]),
					seconds.indexOf(dvalDate[5])
				]
			}
			switch(mode){
				case "date":
					if(flag){
						defaultVal=[returnArr[0],returnArr[1],returnArr[2]];
						return {years,months,days,defaultVal}
					}else{
						defaultVal=[
							years.indexOf(dvalDate[0])==-1?0:years.indexOf(dvalDate[0]),
							months.indexOf(dvalDate[1])==-1?0:months.indexOf(dvalDate[1]),
							days.indexOf(dvalDate[2])==-1?0:days.indexOf(dvalDate[2])
						];
						return {years,months,days,defaultVal}
					}
					break;
				case "half":
					areas=[{
						label:"上午",
						value:0
					},{
						label:"下午",
						value:1
					}];
					if(flag){
						defaultVal=[returnArr[0],returnArr[1],returnArr[2],returnArr[3]];
						return {years,months,days,areas,defaultVal}
					}else{
						let idx=0;
						areas.map((v,k)=>{
							if(v.label==dvalDate[3]){
								idx=v.value;
							}
						})
						defaultVal=[
							years.indexOf(dvalDate[0])==-1?0:years.indexOf(dvalDate[0]),
							months.indexOf(dvalDate[1])==-1?0:months.indexOf(dvalDate[1]),
							days.indexOf(dvalDate[2])==-1?0:days.indexOf(dvalDate[2]),
							idx
						];
						return {years,months,days,areas,defaultVal}
					}
					break;	
				case "yearMonth":
					if(flag){
						defaultVal=[returnArr[0],returnArr[1]];
						return {years,months,defaultVal}
					}else{
						defaultVal=[
							years.indexOf(dvalDate[0])==-1?0:years.indexOf(dvalDate[0]),
							months.indexOf(dvalDate[1])==-1?0:months.indexOf(dvalDate[1])
						];
						return {years,months,defaultVal}
					}
					break;
				case "dateTime":
					if(flag){
						defaultVal=returnArr;
					}else{
						if(hasSecond){
							defaultVal=[
								years.indexOf(dvalDate[0])==-1?0:years.indexOf(dvalDate[0]),
								months.indexOf(dvalDate[1])==-1?0:months.indexOf(dvalDate[1]),
								days.indexOf(dvalDate[2])==-1?0:days.indexOf(dvalDate[2]),
								hours.indexOf(dvalDate[3])==-1?0:hours.indexOf(dvalDate[3]),
								minutes.indexOf(dvalDate[4])==-1?0:minutes.indexOf(dvalDate[4]),
								seconds.indexOf(dvalDate[5])==-1?0:seconds.indexOf(dvalDate[5])
							];
						}else{
							defaultVal=[
								years.indexOf(dvalDate[0])==-1?0:years.indexOf(dvalDate[0]),
								months.indexOf(dvalDate[1])==-1?0:months.indexOf(dvalDate[1]),
								days.indexOf(dvalDate[2])==-1?0:days.indexOf(dvalDate[2]),
								hours.indexOf(dvalDate[3])==-1?0:hours.indexOf(dvalDate[3]),
								minutes.indexOf(dvalDate[4])==-1?0:minutes.indexOf(dvalDate[4])
							];
						}
					}
					if(hasSecond){
						return {years,months,days,hours,minutes,seconds,defaultVal}
					}else{
						return {years,months,days,hours,minutes,defaultVal}
					}
					break;
				case "time":
					if(flag){
						defaultVal=[returnArr[3],returnArr[4],returnArr[5]];
					}else{
						if(hasSecond){
							defaultVal=[
								hours.indexOf(dvalDate[0])==-1?0:hours.indexOf(dvalDate[0]),
								minutes.indexOf(dvalDate[1])==-1?0:minutes.indexOf(dvalDate[1]),
								seconds.indexOf(dvalDate[2])==-1?0:seconds.indexOf(dvalDate[2])
							];
						}else{
							defaultVal=[
								hours.indexOf(dvalDate[0])==-1?0:hours.indexOf(dvalDate[0]),
								minutes.indexOf(dvalDate[1])==-1?0:minutes.indexOf(dvalDate[1])
							];
						}
					}
					return {hours,minutes,seconds,defaultVal}
					break;			
			}
		},
		initMonths:(year,disabled)=>{
			let aDate=new Date();
			let dYear=aDate.getFullYear();
			let dMonth=aDate.getMonth()+1;
			let dDate=aDate.getDate();
			let flag=dYear==year?true:false;
			let months=[];
			if(disabled){
				if(flag){
					for(let m=1;m<=dMonth;m++){
						months.push(forMatNum(m));
					};	
				}else{
					for(let m=1;m<=12;m++){
						months.push(forMatNum(m));
					};	
				}
			}else{
				for(let m=1;m<=12;m++){
					months.push(forMatNum(m));
				};
			};
			return months;
		},
		initDays:(year,month,disabled)=>{
			let aDate=new Date();
			let dYear=aDate.getFullYear();
			let dMonth=aDate.getMonth()+1;
			let dDate=aDate.getDate();
			let flag=(dYear==year&&dMonth==month)?true:false;
			let totalDays=new Date(year,month,0).getDate();
			let dates=[];
			if(flag&&disabled){
				for(let d=1;d<=dDate;d++){
					dates.push(forMatNum(d));
				};			
			}else{
				for(let d=1;d<=totalDays;d++){
					dates.push(forMatNum(d));
				};
			};
			return dates;
		},
	},
	//短期日期上下午
	limitHour:{
		init(dayStep=7,dVal){
			let startDate=new Date();
			let date=[],areas=[],hours=[];
			let hour=new Date().getHours();
			let weeks=["周日","周一","周二","周三","周四","周五","周六"];
			let arrs=[];
			let defaultVal=[];
			let d=0,a=0,h=0;
			for(let i=0;i<dayStep;i++){
				let year,month,day,weekday;
				year=startDate.getFullYear();
				month=forMatNum(startDate.getMonth()+1);
				day=forMatNum(startDate.getDate());
				weekday=weeks[startDate.getDay()];
				let label="";
				switch(i){
					case 0:
						label="今天";
						break;
					case 1:
						label="明天"
						break;
					case 2:
						label="后天"
						break;
					default:
						label=month+"月"+day+"日"+" "+weekday;
						break;
				}
				date.push({
					label:label,
					value:year+"-"+month+"-"+day,
					today:i==0?true:false
				})
				startDate.setDate(startDate.getDate()+1);
			}
			if(hour>12){
				areas=[{
					label:"下午",
					value:1
				}]
			}else{
				areas=[{
					label:"上午",
					value:0
				},{
					label:"下午",
					value:1
				}]
			};
			for(let k=hour>12?hour-12:hour;k<=12;k++){
				hours.push({
					label:forMatNum(k),
					value:forMatNum(hour>12?k+12:k)
				})
			};
			date.map((v,k)=>{
				if(v.label==dVal[0]){
					d=k
				}
			})
			if(d!=0){
				areas=this.initAreas(date[d]);
				hours=this.initHours(date[d],areas[a]);
			}
			areas.map((v,k)=>{
				if(v.label==dVal[1]){
					a=k
				}
			})
			hours.map((v,k)=>{
				if(v.label==dVal[2]){
					h=k
				}
			});
			defaultVal=[d,a,h]
			return {date,areas,hours,defaultVal};
		},
		initAreas(date){
			let areas=[];
			let hour=new Date().getHours();
			if(date.today){
				if(hour>12){
					areas=[{
						label:"下午",
						value:1
					}]
				}else{
					areas=[{
						label:"上午",
						value:0
					},{
						label:"下午",
						value:1
					}]
				};
			}else{
				areas=[{
					label:"上午",
					value:0
				},{
					label:"下午",
					value:1
				}]
			}
			return areas;
		},
		initHours(dateCol,hourCol){
			let hours=[];
			let hour=new Date().getHours();
			if(dateCol.today){
				if(hourCol.value==1&&hour<=12){
					for(let k=1;k<=12;k++){
						hours.push({
							label:forMatNum(k),
							value:forMatNum(hourCol.value==1?k+12:k)
						})
					};
				}else{
					for(let k=hour>12?hour-12:hour;k<=12;k++){
						hours.push({
							label:forMatNum(k),
							value:forMatNum(hourCol.value==1?k+12:k)
						})
					};
				}
				
			}else{
				for(let k=1;k<=12;k++){
					hours.push({
						label:forMatNum(k),
						value:forMatNum(hourCol.value==1?k+12:k)
					})
				};
			};
			return hours
		}
	},
	//短期日期时间初始化
	limit:{
		init(dayStep=7,startHour=8,endHour=20,minuteStep=1,afterStep=30,dVal){
			let startDate=new Date();
			let bsDate=new Date(new Date().getTime()+afterStep*60*1000);
			let date=[],hours=[],minutes=[];
			let hour=bsDate.getHours();
			let minute=Math.floor(bsDate.getMinutes()/minuteStep)*minuteStep;
			let weeks=["周日","周一","周二","周三","周四","周五","周六"];
			let d=0,h=0,m=0;
			let defaultVal=[];
			for(let i=0;i<dayStep;i++){
				let year,month,day,weekday;
				year=startDate.getFullYear();
				month=forMatNum(startDate.getMonth()+1);
				day=forMatNum(startDate.getDate());
				weekday=weeks[startDate.getDay()];
				let label="";
				switch(i){
					case 0:
						label="今天";
						break;
					case 1:
						label="明天"
						break;
					case 2:
						label="后天"
						break;
					default:
						label=month+"月"+day+"日"+" "+weekday;
						break;
				}
				date.push({
					label:label,
					value:year+"-"+month+"-"+day,
					flag:i==0?true:false
				})
				startDate.setDate(startDate.getDate()+1);
			}
			if(hour<startHour){
				hour=startHour;
			};
			if(hour>endHour){
				hour=endHour;
			};
			for(let k=hour*1;k<=endHour*1;k++){
				hours.push({
					label:forMatNum(k),
					value:forMatNum(k),
					flag:k==hour?true:false
				})
			};
			for(let j=minute;j<60;j+=minuteStep*1){
				minutes.push({
					label:forMatNum(j),
					value:forMatNum(j)
				});
			}
			date.map((v,k)=>{
				if(v.label==dVal[0]){
					d=k
				}
			})
			if(d!=0){
				hours=this.initHours(startHour=8,endHour=20,minuteStep=1,afterStep=30,date[d].value);
			}
			hours.map((v,k)=>{
				if(v.label==dVal[1]){
					h=k
				}
			})
			minutes.map((v,k)=>{
				if(v.label==dVal[2]){
					m=k
				}
			})
			defaultVal=[d,h,m];
			return {date,hours,minutes,defaultVal};
		},
		initHours(startHour=8,endHour=20,minuteStep=1,afterStep=30,date){
			let hours=[];
			let arr=date.split("-");
			let aDate=new Date();
			let dYear=aDate.getFullYear();
			let dMonth=aDate.getMonth()+1;
			let dDate=aDate.getDate();
			let bsDate=new Date(new Date().getTime()+afterStep*60*1000);
			let hour=bsDate.getHours();
			let flag=(dYear==arr[0]&&dMonth==arr[1]&&dDate==arr[2])?true:false;
			if(hour>endHour){
				hour=endHour;
			};
			if(flag){
				for(let k=hour*1;k<=endHour*1;k++){
					hours.push({
						label:forMatNum(k),
						value:forMatNum(k),
						flag:k==hour?true:false
					})
				};			
			}else{
				for(let k=startHour*1;k<=endHour*1;k++){
					hours.push({
						label:forMatNum(k),
						value:forMatNum(k),
						flag:false
					})
				}			
			};
			return hours;
		},
		initMinutes(startHour=8,endHour=20,minuteStep=1,afterStep=30,date,hour){
			let minutes=[];
			let bsDate=new Date(new Date().getTime()+afterStep*60*1000);
			let arr=date.split("-");
			let aDate=new Date();
			let dYear=aDate.getFullYear();
			let dMonth=aDate.getMonth()+1;
			let dDate=aDate.getDate();
			let dHour=bsDate.getHours();;
			let minute=Math.floor(bsDate.getMinutes()/minuteStep)*minuteStep;
			let flag=(dYear==arr[0]&&dMonth==arr[1]&&dDate==arr[2])?true:false;
			if(flag){
				if(hour==dHour){
					for(let j=minute;j<60;j+=minuteStep*1){
						minutes.push({
							label:forMatNum(j),
							value:forMatNum(j)
						});
					}	
				}else{
					for(let j=0;j<60;j+=minuteStep*1){
						minutes.push({
							label:forMatNum(j),
							value:forMatNum(j)
						})
					}
				}
						
			}else{
				for(let j=0;j<60;j+=minuteStep*1){
					minutes.push({
						label:forMatNum(j),
						value:forMatNum(j)
					})
				}			
			}
			return minutes;
		}
	},
	//选择区间初始化
	range:{
		init(start,end,value,flag){
			let aToday=new Date();
			let tYear,tMonth,tDay,tHours,tMinutes,tSeconds,defaultVal=[];
			let initstartDate=new Date(start.toString());
			let endDate=new Date(end.toString());
			if(start>end){
				initstartDate=new Date(end.toString());
				endDate=new Date(start.toString());
			};
			let startYear=initstartDate.getFullYear();
			let startMonth=initstartDate.getMonth()+1;
			let endYear=endDate.getFullYear();
			let fyears=[],fmonths=[],fdays=[],tyears=[],tmonths=[],tdays=[],returnArr=[],startDVal=[],endDVal=[];
			startDVal=value[0].split("-");
			endDVal=value[1].split("-");
			let curMonth=flag?startDVal[1]*1:(startDVal[1]+1);
			let totalDays=new Date(startYear,curMonth,0).getDate();
			for(let s=startYear;s<=endYear;s++){
				fyears.push(s+'');
			};
			for(let m=1;m<=12;m++){
				fmonths.push(forMatNum(m));
			};
			for(let d=1;d<=totalDays;d++){
				fdays.push(forMatNum(d));
			};
			for(let s=startDVal[0];s<=endYear;s++){
				tyears.push(s+'');
			};
			if(endDVal[0]>startDVal[0]){
				for(let m=1;m<=12;m++){
					tmonths.push(forMatNum(m));
				};
				for(let d=1;d<=totalDays;d++){
					tdays.push(forMatNum(d));
				};
			}else{
				for(let m=startDVal[1];m<=12;m++){
					tmonths.push(forMatNum(m));
				};
				for(let d=startDVal[2];d<=totalDays;d++){
					tdays.push(forMatNum(d));
				};
			};
			
			defaultVal=[
				fyears.indexOf(startDVal[0])==-1?0:fyears.indexOf(startDVal[0]),
				fmonths.indexOf(startDVal[1])==-1?0:fmonths.indexOf(startDVal[1]),
				fdays.indexOf(startDVal[2])==-1?0:fdays.indexOf(startDVal[2]),
				0,
				tyears.indexOf(endDVal[0])==-1?0:tyears.indexOf(endDVal[0]),
				tmonths.indexOf(endDVal[1])==-1?0:tmonths.indexOf(endDVal[1]),
				tdays.indexOf(endDVal[2])==-1?0:tdays.indexOf(endDVal[2])
			];
			return {
				fyears,
				fmonths,
				fdays,
				tyears,
				tmonths,
				tdays,
				defaultVal
			}
		},
		initStartDays(year,month){
			let totalDays=new Date(year,month,0).getDate();
			let dates=[];
			for(let d=1;d<=totalDays;d++){
				dates.push(forMatNum(d));
			};
			return dates;
		},
		initEndYears(curYear,startYear,endYear){
			let years=[];
			for(let y=curYear;y<=endYear;y++){
				years.push(forMatNum(y));
			};
			return years;
		},
		initEndMonths(curMonth){
			let months=[];
			for(let m=curMonth*1;m<=12;m++){
				months.push(forMatNum(m));
			};
			return months;
		},
		initEndDays(curYear,curMonth,curDate,tYear,tMonth){
			let totalDays=new Date(curYear,curMonth,0).getDate();
			let days=[];
			for(let d=curDate*1;d<=totalDays;d++){
				days.push(forMatNum(d));
			};
			return days;
		},
		initToMonths(curYear,curMonth,curDate,tYear){
			let aDate=new Date(curYear,curMonth,curDate).getTime();
			let bDate=new Date(tYear,curMonth,curDate).getTime();
			let months=[];
			if(bDate-aDate>0){
				console.log(1)
				for(let m=1;m<=12;m++){
					months.push(forMatNum(m));
				};
			}else{
				for(let m=curMonth*1;m<=12;m++){
					months.push(forMatNum(m));
				};
			}
			return months;
		},
		initToDays(curYear,curMonth,curDate,tYear,tMonth){
			let aDate=new Date(curYear,curMonth,curDate).getTime();
			let bDate=new Date(tYear,tMonth,curDate).getTime();
			let totalDays=new Date(tYear,tMonth,0).getDate();
			let days=[];
			if(bDate-aDate>0){
				for(let d=1;d<=totalDays;d++){
					days.push(forMatNum(d));
				};
			}else{
				for(let d=curDate*1;d<=totalDays;d++){
					days.push(forMatNum(d));
				};
			}
			return days;
		}
	}
}

export default initPicker