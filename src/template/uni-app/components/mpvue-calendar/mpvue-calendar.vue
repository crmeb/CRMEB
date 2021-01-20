<template>
  <div class="mpvue-calendar" ref="calendar">
    <div class="calendar-tools" v-if="!isMonthRange">
      <div class="calendar-prev" @click="prev">
        <img :src="arrowLeft" v-if="!!arrowLeft">
        <i class="iconfont icon-arrow-left" v-else></i>
      </div>
      <div class="calendar-next"  @click="next">
        <img :src="arrowRight" v-if="!!arrowRight">
        <i class="iconfont icon-arrow-right" v-else></i>
      </div>
      <div class="calendar-info" @click.stop="changeYear">
        <div class="mc-month">
          <div :class="['mc-month-inner', oversliding ? '' : 'month-transition']" :style="{'top': monthPosition + unit}" v-if="isIos">
            <span v-for="(m, i) in monthsLoop" :key="i" >{{m}}</span>
          </div>
          <div class="mc-month-text" v-else>{{monthText}}</div>
        </div>
        <div class="mc-year">{{year}}</div>
      </div>
    </div>
    <table cellpadding="5">
      <div class="mc-head" :class="['mc-head', {'mc-month-range-mode-head': isMonthRange}]">
        <div class="mc-head-box">
          <div v-for="(week, index) in weeks" :key="index" class="mc-week">{{week}}</div>
        </div>
      </div>
      <div :class="['mc-body', {'mc-range-mode': range, 'week-switch': weekSwitch && !isMonthRange, 'month-range-mode': isMonthRange}]" v-for="(days, index) in monthRangeDays" :key='index'>
        <div class="month-rang-head" v-if="isMonthRange">{{rangeOfMonths[index][2]}}</div>
        <tr v-for="(day,k1) in days" :key="k1" :class="{'gregorianStyle': !lunar}">
          <td v-for="(child,k2) in day" :key="k2" :class="[{'selected': child.selected, 'mc-today-element': child.isToday, 'disabled': child.disabled, 'mc-range-select-one': rangeBgHide && child.selected, 'lunarStyle': lunar, 'mc-range-row-first': k2 === 0 && child.selected, 'month-last-date': child.lastDay, 'month-first-date': 1 === child.day, 'mc-range-row-last': k2 === 6 && child.selected, 'mc-last-month': child.lastMonth, 'mc-next-month': child.nextMonth}, child.className, child.rangeClassName]" @click="select(k1, k2, child, $event, index)" class="mc-day" :style="itemStyle">
            <span v-if="showToday.show && child.isToday" class="mc-today calendar-date">{{showToday.text}}</span>
            <span :class="[{'mc-date-red': k2 === (monFirst ? 5 : 0) || k2 === 6}, 'calendar-date']" v-else>{{child.day}}</span>
            <div class="slot-element" v-if="!!child.content">{{child.content}}</div>
            <div class="mc-text remark-text" v-if="child.eventName && !clean">{{child.eventName}}</div>
            <div class="mc-dot" v-if="child.eventName && clean"></div>
            <div class="mc-text" :class="{'isLunarFestival': child.isAlmanac || child.isLunarFestival,'isGregorianFestival': child.isGregorianFestival,'isTerm': child.isTerm}" v-if="lunar && (!child.eventName || clean)">{{child.almanac || child.lunar}}</div>
            <div class="mc-range-bg" v-if="range && child.selected"/>
          </td>
        </tr>
      </div>
    </table>
    <div class="mpvue-calendar-change" :class="{'show': yearsShow}">
      <div class="calendar-years" v-if="!weekSwitch">
        <span v-for="y in years" :key="y" @click.stop="selectYear(y)" :class="{'active': y === year}">{{y}}</span>
      </div>
      <div :class="['calendar-months', {'calendar-week-switch-months': weekSwitch}]">
        <span v-for="(m, i) in months" :key="m" @click.stop="changeMonth(i)" :class="{'active': i === month}">{{m}}</span>
      </div>
    </div>
  </div>
</template>

<script>
  import calendar from './calendarinit.js';
  import './icon.css';
  const isBrowser = !!window;
  const now = new Date();
  const todayString = [now.getFullYear(), now.getMonth() + 1, now.getDate()].join('-');
  export default {
    props: {
      multi: {
        type: Boolean,
        default: false
      },
      arrowLeft: {
        type: String,
        default: ''
      },
      arrowRight: {
        type: String,
        default: ''
      },
      clean: {
        type: Boolean,
        default: false
      },
      now: {
        type: [String, Boolean],
        default: true
      },
      range:{
        type: Boolean,
        default: false
      },
      completion:{
        type: Boolean,
        default: false
      },
      value: {
        type: Array,
        default: function(){
          return []
        }
      },
      begin:  {
        type: Array,
        default: function(){
          return []
        }
      },
      end:  {
        type: Array,
        default: function(){
          return []
        }
      },
      zero:{
        type: Boolean,
        default: false
      },
      disabled:{
        type: Array,
        default: function(){
          return []
        }
      },
      almanacs:{
        type: Object,
        default: function(){
          return {}
        }
      },
      tileContent:{
        type: Array,
        default: function(){
          return []
        }
      },
      lunar: {
        type: Boolean,
        default: false
      },
      monFirst: {
        type: Boolean,
        default: false
      },
      weeks: {
        type: Array,
        default:function(){
          return this.monFirst ? ['一', '二', '三', '四', '五', '六', '日'] : ['日', '一', '二', '三', '四', '五', '六']
        }
      },
      months:{
        type: Array,
        default:function(){
          return ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月']
        }
      },
      events:  {
        type: Object,
        default: function(){
          return {}
        }
      },
      weekSwitch: {
        type: Boolean,
        default: false
      },
      monthRange: {
        type: Array,
        default: function(){
          return []
        }
      },
      responsive: {
        type: Boolean,
        default: false
      },
      rangeMonthFormat: {
        type: String,
        default: ''
      }
    },
    data() {
      return {
        years:[],
        yearsShow:false,
        year: 0,
        month: 0,
        monthPosition: 0,
        day: 0,
        days: [],
        multiDays:[],
        today: [],
        handleMultiDay: [],
        firstRender: true,
        isIos: true,
        showToday: {},
        monthText: '',
        festival:{
          lunar:{
            "1-1":"春节",
            "1-15":"元宵节",
            "2-2":"龙头节",
            "5-5":"端午节",
            "7-7":"七夕节",
            "7-15":"中元节",
            "8-15":"中秋节",
            "9-9":"重阳节",
            "10-1":"寒衣节",
            "10-15":"下元节",
            "12-8":"腊八节",
            "12-23":"小年",
          },
          gregorian:{
            "1-1":"元旦",
            "2-14":"情人节",
            "3-8":"妇女节",
            "3-12":"植树节",
            "5-1":"劳动节",
            "5-4":"青年节",
            "6-1":"儿童节",
            "7-1":"建党节",
            "8-1":"建军节",
            "9-10":"教师节",
            "10-1":"国庆节",
            "12-24":"平安夜",
            "12-25":"圣诞节",
          },
        },
        rangeBegin: [],
        rangeEnd: [],
        multiDaysData: [],
        monthsLoop: [],
        itemWidth: 50,
        unit: isBrowser ? 'px' : 'rpx',
        positionH: isBrowser ? -24 : -40,
        monthIndex: 0,
        oversliding: false,
        rangeBgHide: false,
        monthRangeDays: [],
        rangeOfMonths: [],
        monthDays: [],
        weekIndex: 0,
        startWeekIndex: 0,
        positionWeek: true,
        isMonthRange: false,
      }
    },
    computed: {
      itemStyle() {
        return {width: this.itemWidth + 'px', height: this.itemWidth + 'px', fontSize: this.itemWidth / 4 + 'px', lineHeight: this.lunar ? (this.itemWidth / 1.5 + 'px') : (this.itemWidth + 'px')}
      }
    },
    watch:{
      events() {
        if (this.isRendeRangeMode()) return;
        this.render(this.year, this.month, '_WATCHRENDER_', 'events');
      },
      disabled() {
        if (this.isRendeRangeMode()) return;
        this.render(this.year, this.month, '_WATCHRENDER_', 'disabled');
      },
      value() {
        if (this.isRendeRangeMode('_WATCHRENDERVALUE_')) return;
        const value = this.value;
        let year = value[0], month = value[1] - 1;
        if (this.multi) {
          year = value[value.length - 1][0];
          month = value[value.length - 1][1] - 1;
        } else if (this.range) {
          if (this.isUserSelect) {
            year = this.year;
            month = this.month;
            this.isUserSelect = false;
          } else {
            year = value[0][0];
            month = value[0][1] - 1;
            const day = value[0][2];
            return this.render(year, month, '_WATCHRENDERVALUE_', [year, month, day]);
          }
        }
        this.render(year, month, '_WATCHRENDERVALUE_');
      },
      tileContent() {
        if (this.isRendeRangeMode()) return;
        this.render(this.year, this.month, '_WATCHRENDER_', 'tileContent');
      },
      almanacs() {
        if (this.isRendeRangeMode()) return;
        this.render(this.year, this.month, '_WATCHRENDER_', 'almanacs');
      },
      monthRange() {
        if (this.isRendeRangeMode()) return;
        this.render(this.year, this.month, '_WATCHRENDER_', 'almanacs');
      },
      responsive() {
        if (this.responsive) this.addResponsiveListener();
      }
    },
    created() {
      this.isMonthRange = !!this.monthRange.length;
      const loopArray = this.months.concat();
      loopArray.unshift(this.months[this.months.length - 1]);
      loopArray.push(this.months[0]);
      this.monthsLoop = loopArray;
      this.monthsLoopCopy = this.monthsLoop.concat();
    },
    mounted() {
      const self = this;
      this.resize();
      if (!isBrowser) {
        wx.getSystemInfo({
          success: function(res) {
            self.isIos = (res.system.split(' ') || [])[0] === 'iOS';
          }
        });
      } else if (this.responsive) {
        this.addResponsiveListener();
      }
      this.oversliding = true;
      this.initRender = true;
      this.init();
    },
    beforeDestroy() {
      if (isBrowser) {
        window.removeEventListener('resize', this.resize)
      }
    },
    methods: {
      init() {
        let now = new Date();
        this.year = now.getFullYear();
        this.month = now.getMonth();
        this.day = now.getDate();
        this.monthIndex = this.month + 1;
        if (this.value.length || this.multi) {
          if (this.range) {
            this.year = Number(this.value[0][0]);
            this.month = this.value[0][1] - 1;
            this.day = Number(this.value[0][2]);
            const yearEnd = Number(this.value[1][0]);
            const monthEnd = this.value[1][1] - 1;
            const dayEnd = this.value[1][2];
            this.rangeBegin = [this.year, this.month, this.day];
            this.rangeEnd = [yearEnd, monthEnd, dayEnd];
          } else if (this.multi) {
            this.multiDays = this.value;
            const handleMultiDay = this.handleMultiDay;
            if (this.firstRender) {
              this.firstRender = false;
              const thatYear = (this.value[0] || [])[0];
              const thatMonth = (this.value[0] || [])[1];
              if (isFinite(thatYear) && isFinite(thatMonth)) {
                this.month = parseInt(thatMonth) - 1;
                this.year = parseInt(thatYear);
              }
            } else if (this.handleMultiDay.length) {
              this.month = parseInt(handleMultiDay[handleMultiDay.length - 1][1]) - 1;
              this.year = parseInt(handleMultiDay[handleMultiDay.length - 1][0]);
              this.handleMultiDay = [];
            } else {
              this.month = parseInt(this.value[this.value.length - 1][1]) - 1;
              this.year = parseInt(this.value[this.value.length - 1][0]);
            }
            this.day = parseInt((this.value[0] || [])[2]);
          } else {
            this.year = parseInt(this.value[0]);
            this.month = parseInt(this.value[1]) - 1;
            this.day = parseInt(this.value[2]);
          }
        }
        this.updateHeadMonth();
        if (this.isRendeRangeMode()) return;
        this.render(this.year, this.month);
      },
      renderOption(year, month, i, playload) {
        const weekSwitch = this.monthRange.length ? false : this.weekSwitch;
        const seletSplit = this.value;
        const isMonthModeCurrentMonth = !weekSwitch && !playload;
        const disabledFilter = (disabled) => {
          return disabled.find(v => {
            const dayArr = v.split('-');
            return year === Number(dayArr[0]) && month === (dayArr[1] - 1) && i === Number(dayArr[2]);
          });
        };
        if (this.range) {
          const lastDay = new Date(year, month + 1, 0).getDate() === i ? {lastDay: true} : null;
          const options = Object.assign(
            {day: i},
            this.getLunarInfo(year, month + 1, i),
            this.getEvents(year, month + 1, i),
            lastDay
          );
          const {date, day} = options;
          const copyRangeBegin = this.rangeBegin.concat();
          const copyRangeEnd = this.rangeEnd.concat();
          copyRangeBegin[1] = copyRangeBegin[1] + 1;
          copyRangeEnd[1] = copyRangeEnd[1] + 1;
          if (weekSwitch || isMonthModeCurrentMonth) {
            (copyRangeEnd.join('-') === date) && (options.rangeClassName = 'mc-range-end');
            (copyRangeBegin.join('-') === date) && (options.rangeClassName = 'mc-range-begin');
          }
          if (year === copyRangeEnd[0] && (month + 1) === copyRangeEnd[1] && day === (copyRangeEnd[2] - 1)) {
            options.rangeClassName = options.rangeClassName ? ['mc-range-begin', 'mc-range-second-to-last'] : 'mc-range-second-to-last';
          }
          if (this.rangeBegin.length) {
            const beginTime = +new Date(this.rangeBegin[0], this.rangeBegin[1], this.rangeBegin[2]);
            const endTime = +new Date(this.rangeEnd[0], this.rangeEnd[1], this.rangeEnd[2]);
            const stepTime = +new Date(year, month, i);
            if (beginTime <= stepTime && endTime >= stepTime) {
              options.selected = true;
            }
          }
          if (this.begin.length) {
            const beginTime = +new Date(parseInt(this.begin[0]), parseInt(this.begin[1]) - 1, parseInt(this.begin[2]));
            if (beginTime > +new Date(year, month, i)) {
              options.disabled = true;
            }
          }
          if (this.end.length) {
            let endTime = Number(new Date(parseInt(this.end[0]), parseInt(this.end[1]) - 1, parseInt(this.end[2])));
            if (endTime <  Number(new Date(year, month, i))) {
              options.disabled = true;
            }
          }
          if (playload && !weekSwitch) {
            options.disabled = true;
          } else if (this.disabled.length && disabledFilter(this.disabled)) {
            options.disabled = true;
          }
          const monthFirstDay = year + '-' + (month + 1) + '-' + 1;
          const monthLastDay = year + '-' + (month + 1) + '-' + new Date(year, month + 1, 0).getDate();
          (monthFirstDay === date && options.selected && !options.rangeClassName) && (options.rangeClassName = 'mc-range-month-first');
          (monthLastDay === date && options.selected && !options.rangeClassName) && (options.rangeClassName = 'mc-range-month-last');
          this.isCurrentMonthToday(options) && (options.isToday = true);
          (!weekSwitch && playload) && (options.selected = false);
          return options;
        } else if(this.multi) {
          let options;
          if (this.value.find(v => year === v[0] && month === v[1]-1 && i === v[2])){
            options = Object.assign(
              {day: i, selected: true},
              this.getLunarInfo(year, month + 1, i),
              this.getEvents(year, month + 1, i)
            );
          } else {
            options = Object.assign(
              {day: i, selected: false},
              this.getLunarInfo(year, month + 1, i),
              this.getEvents(year, month + 1, i)
            );
            if (this.begin.length) {
              const beginTime = +new Date(parseInt(this.begin[0]), parseInt(this.begin[1]) - 1, parseInt(this.begin[2]));
              if (beginTime > +(new Date(year, month, i))) {
                options.disabled = true;
              }
            }
            if (this.end.length){
              const endTime = +new Date(parseInt(this.end[0]), parseInt(this.end[1]) - 1, parseInt(this.end[2]));
              if (endTime < +(new Date(year, month, i))) {
                options.disabled = true;
              }
            }
            if (playload && !weekSwitch) {
              options.disabled = true;
            } else if (this.disabled.length && disabledFilter(this.disabled)) {
              options.disabled = true;
            }
          }
          if (options.selected && this.multiDaysData.length !== this.value.length) {
            this.multiDaysData.push(options);
          }
          this.isCurrentMonthToday(options) && (options.isToday = true);
          (!weekSwitch && playload) && (options.selected = false);
          return options;
        } else {
          const options = {};
          const monthHuman = month + 1;
          if (seletSplit[0] === year && seletSplit[1] === monthHuman && seletSplit[2] === i) {
            Object.assign(
              options,
              {day: i, selected: true},
              this.getLunarInfo(year, monthHuman, i),
              this.getEvents(year, monthHuman, i)
            );
          } else {
            Object.assign(
              options,
              {day: i, selected: false},
              this.getLunarInfo(year, monthHuman, i),
              this.getEvents(year, monthHuman, i)
            );
            if (this.begin.length) {
              const beginTime = +new Date(parseInt(this.begin[0]), parseInt(this.begin[1]) - 1, parseInt(this.begin[2]));
              if (beginTime > Number(new Date(year, month, i))) {
                options.disabled = true;
              }
            }
            if (this.end.length){
              const endTime = +new Date(parseInt(this.end[0]), parseInt(this.end[1]) - 1, parseInt(this.end[2]));
              if (endTime < +(new Date(year, month, i))) {
                options.disabled = true;
              }
            }
            if (playload && !weekSwitch) {
              options.disabled = true;
            } else if (this.disabled.length && disabledFilter(this.disabled)) {
              options.disabled = true;
            }
          }
          this.isCurrentMonthToday(options) && (options.isToday = true);
          (!weekSwitch && playload) && (options.selected = false);
          return options;
        }
      },
      isCurrentMonthToday(options) {
        const isToday = todayString === options.date;
        if (!isToday) return false;
        return this.weekSwitch ? isToday : (Number(todayString.split('-')[1]) === this.month + 1);
      },
      watchRender(type) {
        const weekSwitch = this.weekSwitch;
        const daysDeepCopy = JSON.parse(JSON.stringify(this.monthDays));
        if (type === 'events') {
          const events = this.events || {};
          Object.keys(events).forEach(value => {
            daysDeepCopy.some(v => v.some(vv => {
              if (vv.date === value) {
                vv.eventName = events[value];
                return true;
              }
            }))
          });
          this.monthDays = daysDeepCopy;
        } else if (type === 'disabled') {
          const disabled = this.disabled || [];
          disabled.forEach(value => {
            daysDeepCopy.some(v => v.some(vv => {
              if (vv.date === value) {
                vv.disabled = true;
                return true;
              }
            }))
          });
        } else if (type === 'almanacs') {
          const almanacs = this.almanacs || {};
          Object.keys(almanacs).forEach(value => {
            daysDeepCopy.some(v => v.some(vv => {
              if (vv.date.slice(5, 20) === value) {
                vv.lunar = almanacs[value];
                return true;
              }
            }))
          });
        } else if (type === 'tileContent') {
          const tileContent = this.tileContent || [];
          tileContent.forEach(value => {
            daysDeepCopy.some(v => v.some(vv => {
              if (vv.date === value.date) {
                vv.className = value.className;
                vv.content = value.content;
                return true;
              }
            }))
          });
        }
        if (weekSwitch) {
          this.monthDays = daysDeepCopy;
          this.days = [daysDeepCopy[this.weekIndex]];
          this.monthRangeDays = [this.days];
        } else {
          this.days = daysDeepCopy;
          this.monthRangeDays = [this.days];
        }
      },
      render(y, m, renderer, payload) {
        const weekSwitch = this.weekSwitch;
        const isCustomRender = renderer === 'CUSTOMRENDER';
        const isWatchRenderValue = renderer === '_WATCHRENDERVALUE_';
        this.year = y;
        this.month = m;
        if (renderer === '_WATCHRENDER_') return this.watchRender(payload);
        if (this.range && isWatchRenderValue) {
          if (!Array.isArray((this.value || [])[0])) {
            this.rangeBegin = [];
            this.rangeEnd = [];
          } else {
            this.rangeBegin = [this.value[0][0], this.value[0][1] - 1, this.value[0][2]];
            this.rangeEnd = [this.value[1][0], this.value[1][1] - 1, this.value[1][2]];
          }
        }
        if (isWatchRenderValue && weekSwitch) {
          this.positionWeek = true;
        }
        if (isCustomRender) {
          this.year = y;
          this.month = m;
          this.positionWeek = true;
          if (weekSwitch && !payload) {
            this.startWeekIndex = 0;
            this.weekIndex = 0;
          }
          this.updateHeadMonth();
        }
        let firstDayOfMonth = new Date(y, m, 1).getDay();
        const lastDateOfMonth = new Date(y, m + 1, 0).getDate();
        let lastDayOfLastMonth = new Date(y, m, 0).getDate();
        this.year = y;
        let i = 1, line = 0, temp = [], nextMonthPushDays = 1;
        for (i; i <= lastDateOfMonth; i++) {
          let day = new Date(y, m, i).getDay();
          let k;
          if (day === 0) {
            temp[line] = [];
          } else if (i === 1) {
            temp[line] = [];
            k = lastDayOfLastMonth - firstDayOfMonth + 1;
            for (let j = 0; j < firstDayOfMonth; j++) { //generate prev month surplus option
              temp[line].push(Object.assign(
                this.renderOption(this.computedPrevYear(y, m), this.computedPrevMonth(false, m), k, 'prevMonth'),
                {lastMonth: true}
              ));
              k++;
            }
          }

          temp[line].push(this.renderOption(y, m, i)); //generate current month option

          if (day === 6 && i < lastDateOfMonth) {
            line++;
          } else if (i === lastDateOfMonth) {
            let k = 1;
            const lastDateOfMonthLength = this.monFirst ? 7 : 6;
            for (let d = day; d < lastDateOfMonthLength; d++) { //generate next month surplus option
              temp[line].push(Object.assign(
                this.renderOption(this.computedNextYear(y, m), this.computedNextMonth(false, m), k, 'nextMonth'),
                {nextMonth: true}
              ));
              k++;
            }
            nextMonthPushDays = k;
          }
        }
        const completion = this.completion;
        if (this.monFirst) {
          if (!firstDayOfMonth) {
            let lastMonthDay = lastDayOfLastMonth;
            const LastMonthItems = [];
            for (let i = 1; i <= 7; i++) {
              LastMonthItems.unshift(Object.assign(
                this.renderOption(this.computedPrevYear(y, m), this.computedPrevMonth(false, m), lastMonthDay, 'prevMonth'),
                {lastMonth: true}
              ));
              lastMonthDay --;
            }
            temp.unshift(LastMonthItems);
          }
          temp.forEach((item, index) => {
            if (!index) {
              return item.splice(0, 1);
            };
            temp[index-1].length < 7 && temp[index-1].push(item.splice(0, 1)[0]);
          });
          if (this.isMonthRange && temp[temp.length - 1][0].nextMonth) {
            temp.splice(temp.length - 1, 1); //if the first day of last line is nextMonth, delete this line
          }
          if (!completion && !weekSwitch) {
            const lastIndex = temp.length - 1;
            const secondToLastIndex = lastIndex - 1;
            const differentMonth = temp[lastIndex][0].date.split('-')[1] !== temp[secondToLastIndex][6].date.split('-')[1];
            differentMonth && temp.splice(lastIndex, 1);
          }
        }
        if (completion && !weekSwitch && temp.length <= 5 && nextMonthPushDays > 0) {
          for (let i = temp.length; i<=5; i++) {
            temp[i] = [];
            let start = nextMonthPushDays + (i - line -1) * 7;
            for (let d = start; d <= start + 6; d++) {
              temp[i].push(Object.assign(
                {day: d, disabled: true,  nextMonth: true},
                this.getLunarInfo(this.computedNextYear(), this.computedNextMonth(true), d),
                this.getEvents(this.computedNextYear(), this.computedNextMonth(true), d)
              ));
            }
          }
        }
        if (this.tileContent.length) {
          temp.forEach((item, index) => {
            item.forEach((v, i) => {
              const contents = this.tileContent.find(val => val.date === v.date);
              if (contents) {
                const {className, content} = contents || {};
                v.className = className;
                v.content = content;
              }
            });
          });
        }
        if (weekSwitch) {
          const tempLength = temp.length;
          const lastLineMonth = temp[tempLength - 1][0].date.split('-')[1]; // last line month
          const secondLastMonth = temp[tempLength - 2][0].date.split('-')[1]; // second-to-last line month
          lastLineMonth !== secondLastMonth && temp.splice(tempLength - 1, 1);
        }
        this.monthDays = temp;
        if (weekSwitch && !this.isMonthRange) {
          if (this.positionWeek) {
            let payloadDay = '';
            let searchIndex = true;
            if (Array.isArray(payload)) { //range
              payloadDay = [payload[0], payload[1] + 1, payload[2]].join('-');
            } else if (this.multi || isWatchRenderValue) {
              if (this.thisTimeSelect) {
                payloadDay = this.thisTimeSelect;
              } else {
                payloadDay = this.multi ? this.value[this.value.length - 1].join('-') : this.value.join('-') ;
              }
            }
            if (payload === 'SETTODAY') {
              payloadDay = todayString;
            } else if (isCustomRender) {
              if (typeof payload === 'string') {
                payloadDay = [y, Number(m) + 1, payload].join('-');
                searchIndex = true;
              } else if (typeof payload === 'number') {
                const setIndex = payload > temp.length ? temp.length - 1 : payload;
                this.startWeekIndex = setIndex;
                this.weekIndex = setIndex;
                this.positionWeek = false;
                searchIndex = false;
              }
            }
            const positionDay = payloadDay || todayString;
            if (searchIndex) {
              temp.some((v, i) => {
                const isWeekNow = v.find(vv => vv.date === positionDay);
                if (isWeekNow) {
                  this.startWeekIndex = i;
                  this.weekIndex = i;
                  return true;
                }
              });
            }
            this.positionWeek = false;
          }
          this.days = [temp[this.startWeekIndex]];
          if (this.initRender) {
            this.setMonthRangeofWeekSwitch();
            this.initRender = false;
          }
        } else {
          this.days = temp;
        }
        const todayText = '今';
        if (typeof this.now === 'boolean' && !this.now) {
          this.showToday = {show: false};
        } else if (typeof this.now === 'string') {
          this.showToday = {
            show: true,
            text: this.now || todayText
          };
        } else {
          this.showToday = {
            show: true,
            text: todayText
          };
        }
        this.monthRangeDays = [this.days];
        isWatchRenderValue && this.updateHeadMonth();
        return this.days;
      },
      rendeRange(renderer) {
        const range = [];
        const self = this;
        const monthRange = this.monthRange;
        function formatDateText(fYear, fMonth) {
          const reg = /([y]+)(.*?)([M]+)(.*?)$/i;
          const rangeMonthFormat = self.rangeMonthFormat || 'yyyy-MM';
          reg.exec(rangeMonthFormat);
          return String(fYear).substring(4 - RegExp.$1.length) + RegExp.$2 + String(fMonth).substring(2 - RegExp.$3.length) + RegExp.$4;
        }
        if (monthRange[0] === monthRange[1]) {
          const [y, m] = monthRange[0].split('-');
          range.push([Number(y), Number(m), formatDateText(y, m)])
        } else {
          const monthRangeOfStart = monthRange[0].split('-');
          const monthRangeOfEnd = monthRange[1].split('-');
          let startYear = +monthRangeOfStart[0];
          let startMonth = +monthRangeOfStart[1];
          let endYear = +monthRangeOfEnd[0];
          let endtMonth = +monthRangeOfEnd[1] > 12 ? 12 : +monthRangeOfEnd[1];
          while (startYear < endYear || startMonth <= endtMonth) {
            range.push([startYear, startMonth, formatDateText(startYear, startMonth)]);
            if (startMonth === 12 && startYear !== endYear) {
              startYear++;
              startMonth = 0;
            }
            startMonth++;
          }
        }
        this.rangeOfMonths = range;

        const monthsRange = range.map(item => {
          const [yearParam, monthParam] = item;
          return this.render(yearParam, monthParam - 1, renderer);
        });
        this.monthRangeDays = monthsRange;
      },
      isRendeRangeMode(renderer) {
        this.isMonthRange = !!this.monthRange.length;
        if (this.isMonthRange) {
          this.rendeRange(renderer);
          return true;
        }
      },
      renderer(y, m, w) {
        const renderY = y || this.year;
        const renderM = typeof parseInt(m) === 'number' ? (m - 1) : this.month;
        this.initRender = true;
        this.render(renderY, renderM, 'CUSTOMRENDER', w);
        !this.weekSwitch && (this.monthsLoop = this.monthsLoopCopy.concat());
      },
      computedPrevYear(year, month) {
        let value = year;
        if((month - 1) < 0){
          value--;
        }
        return value;
      },
      computedPrevMonth(isString, month) {
        let value = month;
        if((month - 1) < 0){
          value = 11;
        } else {
          value--;
        }
        if(isString) {
          return value + 1;
        }
        return value;
      },
      computedNextYear(year, month) {
        let value = year;
        if((month + 1) > 11){
          value++;
        }
        return value;
      },
      computedNextMonth(isString, month) {
        let value = month;
        if((month + 1) > 11){
          value = 0;
        } else {
          value++;
        }
        if(isString) {
          return value + 1;
        }
        return value;
      },
      getLunarInfo(y, m, d) {
        let lunarInfo = calendar.solar2lunar(y, m, d);
        let yearEve = '';
        if (lunarInfo.lMonth === 12 && lunarInfo.lDay === calendar.monthDays(lunarInfo.lYear, 12)) {
          yearEve = '除夕';
        }
        let lunarValue = lunarInfo.IDayCn;
        let Term = lunarInfo.Term;
        let isLunarFestival = false;
        let isGregorianFestival = false;
        if(this.festival.lunar[lunarInfo.lMonth + "-" + lunarInfo.lDay]) {
          lunarValue = this.festival.lunar[lunarInfo.lMonth + "-" + lunarInfo.lDay];
          isLunarFestival = true;
        } else if(this.festival.gregorian[m + "-" + d]) {
          lunarValue = this.festival.gregorian[m + "-" + d];
          isGregorianFestival = true;
        }
        const lunarInfoObj = {
          date: `${y}-${m}-${d}`,
          lunar: yearEve || Term || lunarValue,
          isLunarFestival: isLunarFestival,
          isGregorianFestival: isGregorianFestival,
          isTerm: !!yearEve || lunarInfo.isTerm
        };
        if (Object.keys(this.almanacs).length) {
          Object.assign(lunarInfoObj, {
            almanac: this.almanacs[m + "-" + d] || '',
            isAlmanac: !!this.almanacs[m + "-" + d]
          });
        }
        return lunarInfoObj;
      },
      getEvents(y, m, d){
        if(Object.keys(this.events).length == 0) return false;
        let eventName = this.events[y + "-" + m + "-" + d];
        let data = {};
        if(eventName!=undefined){
          data.eventName = eventName;
        }
        return data;
      },
      prev(e) {
        e && e.stopPropagation();
        if (this.isMonthRange) return;
        const weekSwitch = this.weekSwitch;
        const changeMonth = (changed) => {
          if (this.monthIndex === 1) {
            this.oversliding = false;
            this.month = 11;
            this.year = parseInt(this.year) - 1;
            this.monthIndex = this.monthIndex - 1;
          } else if (this.monthIndex === 0) {
            this.oversliding = true;
            this.monthIndex = 12;
            setTimeout(() => this.prev(e), 50);
            return this.updateHeadMonth('custom');
          } else if (this.monthIndex === 13) {
            this.month = 11;
            this.year = parseInt(this.year) - 1;
            this.monthIndex = this.monthIndex - 1;
          } else {
            this.oversliding = false;
            this.month = parseInt(this.month) - 1;
            this.monthIndex = this.monthIndex - 1;
          }
          this.updateHeadMonth('custom');
          this.render(this.year, this.month);
          (typeof changed === 'function') && changed();
          const weekIndex = weekSwitch ? this.weekIndex : undefined;
          this.$emit('prev', this.year, this.month + 1, weekIndex);
        }
        if (!this.weekSwitch) return changeMonth();
        const changeWeek = () => {
          this.weekIndex = this.weekIndex - 1;
          this.days = [this.monthDays[this.weekIndex]];
          this.monthRangeDays = [this.days];
          this.setMonthRangeofWeekSwitch();
          this.$emit('prev', this.year, this.month + 1, this.weekIndex);
        }
        const currentWeek = (this.days[0] || [])[0] || {};
        if (currentWeek.lastMonth || currentWeek.day === 1) {
          const monthChenged = () => {
            const lastMonthLength = this.monthDays.length;
            const startWeekIndex = currentWeek.lastMonth ? lastMonthLength - 1: lastMonthLength;
            this.startWeekIndex = startWeekIndex;
            this.weekIndex = startWeekIndex;
            changeWeek();
          }
          changeMonth(monthChenged);
        } else {
          changeWeek();
        }
      },
      next(e) {
        e && e.stopPropagation();
        if (this.isMonthRange) return;
        const weekSwitch = this.weekSwitch;
        const changeMonth = () => {
          if (this.monthIndex === 12) {
            this.oversliding = false;
            this.month = 0;
            this.year = parseInt(this.year) + 1;
            this.monthIndex = this.monthIndex + 1;
          } else if (this.monthIndex === 0 && this.month === 11) {
            this.oversliding = false;
            this.month = 0;
            this.year = parseInt(this.year) + 1;
            this.monthIndex = this.monthIndex + 1;
          } else if (this.monthIndex === 13) {
            this.oversliding = true;
            this.monthIndex = 1;
            setTimeout(() => this.next(e), 50);
            return this.updateHeadMonth('custom');
          } else {
            this.oversliding = false;
            this.month = parseInt(this.month) + 1;
            this.monthIndex = this.monthIndex + 1;
          }
          this.updateHeadMonth('custom');
          this.render(this.year, this.month);
          const weekIndex = weekSwitch ? this.weekIndex : undefined;
          this.$emit('next', this.year, this.month + 1, weekIndex);
        }
        if (!this.weekSwitch) return changeMonth();
        const changeWeek = () => {
          this.weekIndex = this.weekIndex + 1;
          this.days = [this.monthDays[this.weekIndex]];
          this.monthRangeDays = [this.days];
          this.setMonthRangeofWeekSwitch();
          this.$emit('next', this.year, this.month + 1, this.weekIndex);
        }
        const currentWeek = (this.days[0] || [])[6] || {};
        if (currentWeek.nextMonth || currentWeek.day === (new Date(this.year, this.month + 1, 0).getDate())) {
          const startWeekIndex = currentWeek.nextMonth ? 1 : 0;
          this.startWeekIndex = startWeekIndex;
          this.weekIndex = startWeekIndex;
          changeMonth();
        } else {
          changeWeek();
        }
      },
      select(k1, k2, data, e, monthIndex) {
        e && e.stopPropagation();
        const weekSwitch = this.weekSwitch;
        if (data.lastMonth && !weekSwitch) {
          return this.prev(e);
        } else if (data.nextMonth && !weekSwitch) {
          return this.next(e);
        }
        if (data.disabled) return;
        (data || {}).event = (this.events || {})[data.date] || '';
        const {selected, day, date} = data;
        const selectedDates = date.split('-');
        const selectYear = Number(selectedDates[0]);
        const selectMonth = selectedDates[1] - 1;
        const selectMonthHuman = Number(selectedDates[1]);
        const selectDay = Number(selectedDates[2]);;
        if (this.range) {
          this.isUserSelect = true;
          if (this.rangeBegin.length === 0 || this.rangeEndTemp !== 0) {
            this.rangeBegin = [selectYear, selectMonth, selectDay];
            this.rangeBeginTemp = this.rangeBegin;
            this.rangeEnd = [selectYear, selectMonth, selectDay];
            this.thisTimeSelect = this.rangeEnd;
            this.rangeEndTemp = 0;
          } else {
            this.rangeEnd = [selectYear, selectMonth, selectDay];
            this.thisTimeSelect = [selectYear, selectMonth, selectDay];
            if (this.rangeBegin.join('-') === this.rangeEnd.join('-')) {
              return this.rangeEndTemp = 0;
            }
            this.rangeEndTemp = 1;
            if (+new Date(this.rangeEnd[0], this.rangeEnd[1], this.rangeEnd[2]) < +new Date(this.rangeBegin[0], this.rangeBegin[1], this.rangeBegin[2])) {
              this.rangeBegin = this.rangeEnd;
              this.rangeEnd = this.rangeBeginTemp;
            }
            const rangeDate = (date) => {
              return date.map((v, k) =>{
                const value = k === 1 ? v + 1 : v;
                return this.zero ? this.zeroPad(value) : value;
              });
            }
            const begin = rangeDate(this.rangeBegin);
            const end = rangeDate(this.rangeEnd);
            this.value.splice(0, 1, begin)
            this.value.splice(1, 1, end)
            this.$emit('select', begin, end);
          }
          this.rangeBgHide = !this.rangeEndTemp || (this.rangeBegin.join('-') === this.rangeEnd.join('-'));
          this.positionWeek = true;
          if (this.isMonthRange) {
            this.rendeRange();
          } else {
            this.render(this.year, this.month, undefined, this.thisTimeSelect);
          }
        } else if (this.multi) {
          const filterDayIndex = this.value.findIndex(v => v.join('-') === date);
          if(~filterDayIndex) {
            this.handleMultiDay = this.value.splice(filterDayIndex, 1);
          } else {
            this.value.push([Number(Number(selectedDates[0])), Number(selectedDates[1]), day]);
          }
          this.days[k1][k2].selected = !selected;
          if (this.monthDays[k1][k2].selected) {
            this.multiDaysData.push(data);
          } else {
            this.multiDaysData = this.multiDaysData.filter(item => item.date !== date);
          }
          this.thisTimeSelect = date;
          this.$emit('select', this.value, this.multiDaysData);
        } else {
          const currentSelected = this.value.join('-');
          this.monthRangeDays.some(value => value.some(v => !!v.find(vv => {
            if (vv.date === currentSelected) {
              vv.selected = false;
              return true;
            }
          })));
          this.monthRangeDays[monthIndex][k1][k2].selected = true;
          this.day = day;
          const selectDate = [selectYear, selectMonthHuman, selectDay];
          this.value[0] = selectYear;
          this.value[1] = selectMonthHuman;
          this.value[2] = selectDay;
          this.today = [k1, k2];
          this.$emit('select', selectDate, data);
        }
      },
      changeYear() {
        if(this.yearsShow) {
          this.yearsShow = false;
          return false;
        }
        this.yearsShow = true;
        this.years = [];
        for (let i = this.year - 5; i < this.year + 7; i++){
          this.years.push(i);
        }
      },
      changeMonth(value) {
        this.oversliding && (this.oversliding = false);
        this.yearsShow = false;
        this.month = value;
        this.render(this.year, this.month, 'CUSTOMRENDER', 0);
        this.updateHeadMonth();
        this.weekSwitch && this.setMonthRangeofWeekSwitch();
        this.$emit('selectMonth', this.month + 1, this.year);
      },
      selectYear(value) {
        this.yearsShow = false;
        this.year = value;
        this.render(this.year, this.month);
        this.$emit('selectYear', value);
      },
      setToday() {
        const now = new Date();
        this.year = now.getFullYear();
        this.month = now.getMonth();
        this.day = now.getDate();
        this.positionWeek = true;
        this.render(this.year, this.month, undefined, 'SETTODAY');
        this.updateHeadMonth();
      },
      setMonthRangeofWeekSwitch() {
        this.monthsLoop = this.monthsLoopCopy.concat();
        this.days[0].reduce((prev, current) => {
          if (!prev) return;
          const prveDate = ((prev || {}).date || '').split('-');
          const prevYear = prveDate[0];
          const prevMonth = prveDate[1];
          const currentMonth = ((current || {}).date || '').split('-')[1];
          if (prevMonth === currentMonth) {
            return current;
          } else {
            const prevMonthText = this.months[prevMonth - 1];
            const currentMonthText = this.months[currentMonth - 1];
            this.monthsLoop[this.monthIndex] = prevMonthText + '~' + currentMonthText;
          }
        });
      },
      dateInfo(y, m, d) {
        return calendar.solar2lunar(y, m, d);
      },
      zeroPad(n) {
        return String(n < 10 ? '0' + n : n)
      },
      updateHeadMonth(type) {
        if (!type) this.monthIndex = this.month + 1;
        this.monthPosition = this.monthIndex * this.positionH;
        this.monthText = this.months[this.month];
      },
      addResponsiveListener() {
        window.addEventListener('resize', this.resize);
      },
      resize() {
        const calendar = this.$refs.calendar;
        this.itemWidth = (calendar.clientWidth/7 - 4).toFixed(5);
      }
    }
  }
</script>
