;(function ($) {
  'use strict'

  $(document).ready(function () {
    if ($('#event-calendar').length > 0) {
      let loop = 0
      let isMonthChanged = false
      let firstChange = 0

      const restUrl = document.location.origin + '/wp-json/'
      const request = $.get(restUrl + 'custom/v1/calendarevents')

      let calA = new Calendar({
        id: '#event-calendar',
        // small or large
        calendarSize: 'large',
        // an array of layout modifiers
        layoutModifiers: ['month-left-align'],
        // basic | glass
        theme: 'glass',
        // custom colors
        primaryColor: '#76B829',
        headerColor: '#343A40',
        headerBackgroundColor: 'transparent',
        weekdaysColor: 'based on theme',
        // short | long-lower | long-upper
        weekdayDisplayType: 'short',
        // short | long
        monthDisplayType: 'long',
        // 0 (Sun)
        startWeekday: 1,
        // font family
        //fontFamilyHeader: 'based on theme',
        //fontFamilyWeekdays: 'based on theme',
        //fontFamilyBody: 'based on theme',
        // shadow CSS
        dropShadow: 'none',
        // border CSS
        //border: 'based on theme',
        // border radius
        borderRadius: '0',
        // disable month year pickers
        disableMonthYearPickers: true,
        // disable click on dates
        disableDayClick: false,
        // disable the arrows to navigate between months
        disableMonthArrowClick: false,
        customMonthValues: [
          'Janvier',
          'Février',
          'Mars',
          'Avril',
          'Mai',
          'Juin',
          'Juillet',
          'Aout',
          'Septembre',
          'Octobre',
          'Novembre',
          'Décembre'
        ],
        customWeekdayValues: ['D', 'L', 'M', 'M', 'J', 'V', 'S'],
        eventsData: [],
        selectedDateClicked: (currentDate, events) => {
          loop = 4
          isMonthChanged = false
          console.log('Test', currentDate, events, loop)
          calA.setDate(currentDate)
          $('#calendar-results').html('')
          const clickedDate = new Date(currentDate)
          const da = ('0' + clickedDate.getDate()).slice(-2) //day
          const mon = ('0' + (clickedDate.getMonth() + 1)).slice(-2) //month
          const yr = clickedDate.getFullYear() //year
          $('#calendar-results').append(`
              <h4 class="dayTitle">Activités du ${da}/${mon}/${yr}</h4>
            `)
          events.forEach((post) => {
            $('#calendar-results').append(`
                <div class="dayEvent">
                  <div><a href="${post.link}">${post.name}</a></div>
                  <div class="smallTxt">${post.commune}</div>
                </div>
              `)
          })

          if (events.length > 0) {
            var modal = document.getElementById('myModal')
            modal.style.display = 'block'
          }
        },
        dateChanged: (currentDate, events) => {
          loop++

          console.log(
            'date change !',
            currentDate,
            events.length,
            loop,
            isMonthChanged
          )

          $('#calendar-results').html('')
          const clickedDate = new Date(currentDate)
          const da = ('0' + clickedDate.getDate()).slice(-2) //day
          const mon = ('0' + (clickedDate.getMonth() + 1)).slice(-2) //month
          const yr = clickedDate.getFullYear() //year
          $('#calendar-results').append(`
              <h4 class="dayTitle">Activités du ${da}/${mon}/${yr}</h4>
            `)
          events.forEach((post) => {
            $('#calendar-results').append(`
                <div class="dayEvent">
                  <div><a href="${post.link}">${post.name}</a></div>
                  <div class="smallTxt">${post.commune}</div>
                </div>
              `)
          })

          if (!isMonthChanged) {
            isMonthChanged = false
          }

          if (loop > 2 && !isMonthChanged && events.length > 0) {
            var modal = document.getElementById('myModal')
            modal.style.display = 'block'
          }

          if (loop > 2 && isMonthChanged) {
            isMonthChanged = false
          }
        },
        monthChanged: (currentDate, events) => {
          if (firstChange > 1) isMonthChanged = true

          firstChange++

          console.log(
            'month change !',
            currentDate,
            events,
            firstChange,
            isMonthChanged
          )
        }
      })

      $.when(request).done(function (response) {
        calA.addEventsData(response)
      })

      // Get the <span> element that closes the modal
      var span = document.getElementsByClassName('close')[0]

      // When the user clicks on <span> (x), close the modal
      span.onclick = function () {
        var modal = document.getElementById('myModal')
        modal.style.display = 'none'
      }

      // When the user clicks anywhere outside of the modal, close it
      window.onclick = function (event) {
        var modal = document.getElementById('myModal')
        if (event.target == modal) {
          modal.style.display = 'none'
        }
      }
    }
  })
})(jQuery)
