;(function ($) {
  'use strict'

  $(document).ready(function () {
  	$( ".jet-form-builder-row:has(.controlspam)" ).addClass( "controlspam" );
  
    $(".jet-select__control option[value='1']").remove()

    $('.jet-engine-listing-overlay-link').on('click', function (e) {
      const link = $(this).attr('href')

      // Check "open in new window/tab" key modifiers
      if (e.ctrlKey || e.metaKey) {
        e.preventDefault()
        window.open(link, '_blank')
      }
    })

    $('.jet-engine-listing-overlay-wrap').removeAttr('data-url')

    $('.btnOffresEmploi a').each(function () {
      $(this).attr(
        'href',
        $(this).attr('href') +
          '?jsf=jet-engine:liste-actualites&meta=type:Offres d’emploi'
      )
    })

    $('.publication').each(function () {
      const lien = $(this).find('.lien-publication a').attr('href')
      $(this).find('.btn-publication a').attr('href', lien)
    })

    //Ajouter attribut title aux images
    $('.picto img').each(function () {
      const alt = $(this).attr('alt')
      $(this).attr('title', alt)
    })

    //Remplacer @ par [a] pour les emails identifiés
    $('.emailClean a').each(function () {
      const email = $(this).find('span').html()
      $(this).find('span').html(email.replace('@', '[a]'))
    })

    //Gestion des encarts : liens...
    $('.lien_encart').each(function () {
      if ($(this).attr('href')) {
        $(this).removeClass('Cacher')
      }
    })

    //Code page s'engager et pages enfants : gestion du menu
    function menuTopClick(element) {
      const id = element.attr('id')
      if (id === 'education') {
        //Pour le menu Education seulement
        $('.submenuContent a.elementor-item').each(function (e) {
          $(this).removeClass('elementor-item-active')
        })
        $('.pagesEnfants').each(function (e) {
          if ($(this).attr('id') !== id + '-pages') {
            $(this).addClass('Cacher')
          } else {
            $(this).removeClass('Cacher')
          }
        })
      }

      $('.btnPlus').removeClass('Cacher')

      $('.submenuContent').each(function () {
        if ($(this).attr('id') !== id + 'Sub') {
          $(this).addClass('Cacher')
        } else {
          $('#' + id + 'Sub').toggleClass('Cacher')
        }
      })

      if ($('#' + id + 'Sub').hasClass('Cacher')) {
        element.find('.btnPlus').removeClass('Cacher')
      } else {
        element.find('.btnPlus').addClass('Cacher')

        if (id !== 'education') {
          //on sélectionne automatiquement le premier niveau
          subMenuClick($('#' + id + 'Sub ul li:first-child'))
        }
      }
    }

    function subMenuClick(element) {
      const classNameList = element.prop('classList')
      let id = 0

      $('.submenuContent  a.elementor-item').each(function (e) {
        $(this).removeClass('elementor-item-active')
      })

      element.find('a').addClass('elementor-item-active')

      $.each(classNameList, function (key, value) {
        if (value.indexOf('post-') === 0) {
          id = value.replace('post-', '')
        }
      })

      $('.pagesEnfants').each(function (e) {
        if ($(this).attr('id') !== id) {
          $(this).addClass('Cacher')
        } else {
          $(this).removeClass('Cacher')
        }
      })
    }

    const bodyClassNameList = $('body').prop('classList')
    let pageId = 0
    $.each(bodyClassNameList, function (key, value) {
      if (value.indexOf('page-id-') === 0) {
        pageId = value.replace('page-id-', '')
      }
    })

    switch (pageId) {
      case '1392':
        $('#citoyenSub ul li:first-child a').addClass('elementor-item-active')
        menuTopClick($('#citoyen'))
        subMenuClick($('#citoyenSub ul li:first-child'))
        break

      case '526':
        $('#citoyenSub ul li:first-child a').addClass('elementor-item-active')
        menuTopClick($('#citoyen'))
        subMenuClick($('#citoyenSub ul li:first-child'))
        break

      case '528':
        $('#entrepriseSub ul li:first-child a').addClass(
          'elementor-item-active'
        )
        menuTopClick($('#entreprise'))
        subMenuClick($('#entrepriseSub ul li:first-child'))
        break

      case '530':
        $('#collectiviteSub ul li:first-child a').addClass(
          'elementor-item-active'
        )
        menuTopClick($('#collectivite'))
        subMenuClick($('#collectiviteSub ul li:first-child'))
        break

      case '532':
        menuTopClick($('#education'))
        break

      default:
        $('li.post-' + pageId + ' a').addClass('elementor-item-active')
        menuTopClick(
          $('li.post-' + pageId + ' a')
            .closest('.blocMenu')
            .find('.menuTop')
        )
        subMenuClick($('li.post-' + pageId))
        break
    }

    $('.menuTop').click(function () {
      menuTopClick($(this))
    })

    $('.submenuContent  a.elementor-item').each(function (e) {
      $(this).attr('href', 'javascript:void(0)')
    })

    $('.menuTop  a').each(function (e) {
      $(this).attr('href', 'javascript:void(0)')
    })

    $('.submenuContent nav > ul > li.menu-item').click(function (e) {
      e.stopPropagation()
      subMenuClick($(this))
    })

    //Code pour pages DT : accordéons, boutons redirection vers agenda, actus pré-filtrées
    function accordeonTitreClick(element) {
      if (element.closest('.blocDT').find('.accordeonContenu').is(':visible')) {
        element.find('.chevronUp').hide()
        element.find('.chevronDown').show()
        element.closest('.blocDT').find('.accordeonContenu').slideUp()
      } else {
        element.find('.chevronDown').hide()
        element.find('.chevronUp').show()
        element.closest('.blocDT').find('.accordeonContenu').slideDown()
      }
    }

    $('.accordeonTitre').click(function () {
      accordeonTitreClick($(this))
    })

    if ($('body').hasClass('actions-template-default')) {
      //Si on est sur une page Action de DT il faut griser le menu nos actions de la DT
      $('#menuDT ul li:nth-child(3) a').addClass('elementor-item-active')
    }

    $('.btnDTActualites a').each(function () {
      const lien = $(this).attr('href')
      $(this).attr(
        'href',
        lien + '?jsf=jet-engine:liste-actualites&tax=category:24'
      )
    })

    $('.btnDTOffres a').each(function () {
      const lien = $(this).attr('href')
      $(this).attr(
        'href',
        lien + '?jsf=jet-engine:liste-actualites&meta=type:Offres de bénévolat'
      )
    })

    //Code Page Qui-sommes nous ?
    //Gestion du chamsp select Salariés
    if ($('#choix_salaries').length > 0) {
      $('#choix_salaries').change(function () {
        $('.bloc_salaries').hide()
        const val = $(this).find(':selected').val()
        $('#salaries_' + val).show()
        initSectionsPos()
      })
    }

    //Gestion Cartes SVG et menu_lpo_locales
    function loadFunctionsCarteSVG() {
      $('.svg_tooltip')
        .hover(
          function (e) {
            // Hover event
            const titleText = $(this).attr('title')
            $(this).data('tiptext', titleText).removeAttr('title')
            $('<p class="tooltip"></p>')
              .text(titleText)
              .appendTo('body')
              .css('top', e.pageY - 10 + 'px')
              .css('left', e.pageX + 20 + 'px')
              .show()
          },
          function () {
            // Hover off event
            $(this).attr('title', $(this).data('tiptext'))
            $('.tooltip').remove()
          }
        )
        .mousemove(function (e) {
          // Mouse move event
          $('.tooltip')
            .css('top', e.pageY - 10 + 'px')
            .css('left', e.pageX + 20 + 'px')
        })

      $('.CarteDT .svg_departement').click(function () {
        const id = $(this).attr('id')
        const url = location.protocol + '//' + location.host + '/'
        window.location = url + 'lpo-locales/' + id
      })
      $('.CarteDT .svg_dt_auvergne').click(function () {
        const id = $(this).attr('id')
        const url = location.protocol + '//' + location.host + '/'
        window.location = url + 'lpo-locales/' + id
      })
      $('.CarteDT .svg_dt_drome_ardeche').click(function () {
        const id = $(this).attr('id')
        const url = location.protocol + '//' + location.host + '/'
        window.location = url + 'lpo-locales/' + id
      })
      /* Pour Carte SVG des groupes locaux */
      $('#CarteGroupes .svg_departement').click(function () {
        const id = $(this).attr('id')
        const url = location.protocol + '//' + location.host + '/'
        window.location = url + 'lpo-locales/' + id + '/benevolat'
      })
      $('#CarteGroupes .svg_dt_auvergne').click(function () {
        const id = $(this).attr('id')
        const url = location.protocol + '//' + location.host + '/'
        window.location = url + 'lpo-locales/' + id + '/benevolat'
      })
      $('#CarteGroupes .svg_dt_drome_ardeche').click(function () {
        const id = $(this).attr('id')
        const url = location.protocol + '//' + location.host + '/'
        window.location = url + 'lpo-locales/' + id + '/benevolat'
      })
    }

    /* Pour Carte SVG des DT */
    loadFunctionsCarteSVG()

    $('#menu_lpo_locales').click(function (e) {
      $('#wrapper_menu_lpo_locales').toggle(0, function () {
        if ($('#wrapper_menu_lpo_locales').is(':visible')) {
          console.log('Ouverture Carte')
          //loadFunctionsCarteSVG();
        }
      })
    })

    $('#close_menu_lpo_locales').click(function (e) {
      $('#wrapper_menu_lpo_locales').toggle('slow', function () {})
    })

    //Sélection formulaire newsletter SendInBlue
    if ($('#choix_newsletter').length > 0) {
      var forms = []

      $('.bloc_newsletter').each(function (index) {
        var id = $(this).attr('id')
        forms[id] = $(this)
      })

      $('#choix_newsletter').change(function () {
        $('.bloc_newsletter').remove()
        const val = $(this).find(':selected').val()
        console.log(forms['newsletter_' + val])
        $('#forms_sib_container').append(forms['newsletter_' + val])
      })

      $('#choix_newsletter').trigger('change')
    }

    //Gestion du sous menu et du scroll entre sections
    function initSectionsPos() {
      section1Pos =
        $('#section1').length > 0 ? $('#section1').offset().top - 160 : 0
      section2Pos =
        $('#section2').length > 0 ? $('#section2').offset().top - 160 : 0
      section3Pos =
        $('#section3').length > 0 ? $('#section3').offset().top - 160 : 0
      section4Pos =
        $('#section4').length > 0 ? $('#section4').offset().top - 160 : 0
      section5Pos =
        $('#section5').length > 0 ? $('#section5').offset().top - 160 : 0 //dernière section
    }

    let section1Pos = 0
    let section2Pos = 0
    let section3Pos = 0
    let section4Pos = 0
    let section5Pos = 0
    initSectionsPos()

    $(window).scroll(function () {
      const winTop = $(window).scrollTop()
      $(':focus').blur()

      $('.nav_item').each(function () {
        $(this).find('a').removeClass('section_active')
      })

      if (winTop >= section1Pos && winTop <= section2Pos) {
        $('.nav_item').each(function () {
          $(this).find('a').removeClass('section_active')
        })

        $('#lien_section1').addClass('section_active')
      } else if (winTop >= section2Pos && winTop <= section3Pos) {
        $('#lien_section2').addClass('section_active')
      } else if (winTop >= section3Pos && winTop <= section4Pos) {
        $('#lien_section3').addClass('section_active')
      } else if (winTop >= section4Pos && winTop <= section5Pos) {
        $('#lien_section4').addClass('section_active')
      } else if (winTop >= section5Pos) {
        $('#lien_section5').addClass('section_active')
      }
    })
  })
})(jQuery)