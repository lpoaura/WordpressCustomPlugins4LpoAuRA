;(function ($) {
  'use strict'
  
  function cleanSoustheme() {
    const label = $('#theme-principal option:selected').text()
    const code = label.substr(0, label.indexOf('_'))
    const souslabel = $('#sous-theme option:selected').text()
    const souscode = souslabel.substr(0, souslabel.indexOf('_'))

    let _label = ''
    let _code = ''
    let loop = 1

    $('#sous-theme > option').each(function () {
      _label = this.text
      _code = _label.substr(0, label.indexOf('_'))

      if (_code === code) {
        $(this).removeClass('hiddenOption')

        if (loop === 1 && souscode != code) {
          $('#sous-theme').val(this.value).change()
        }

        loop++
      } else {
        $(this).addClass('hiddenOption')
      }
    })
  }

  function cleanPublic() {
    const label = $('#categorie-public option:selected').text()
    const code = label.substr(0, label.indexOf('_'))
    const souslabel = $('#type-public option:selected').text()
    const souscode = souslabel.substr(0, souslabel.indexOf('_'))

    let _label = ''
    let _code = ''
    let loop = 1

    $('#type-public > option').each(function () {
      _label = this.text
      _code = _label.substr(0, label.indexOf('_'))

      if (_code === code) {
        $(this).removeClass('hiddenOption')

        if (loop === 1 && souscode != code) {
          $('#type-public').val(this.value).change()
        }

        loop++
      } else {
        $(this).addClass('hiddenOption')
      }
    })
  }

  $(document).ready(function () {
  	$('.components-button.editor-post-taxonomies__hierarchical-terms-add').hide();
  
    if ($('#theme-principal').length > 0) {
      //On est sur la page d'un evenement

      //Rendre image obligatoire pour evenements
      if ($('input#post_type').val() === 'evenements') {
        if ($('input#image').val() === '') {
          $('input#publish').prop('disabled', true)
          $('input#publish').val('Veuillez ajouter une image')
        } else {
          $('input#publish').prop('disabled', false)
          $('input#publish').val($('input#original_publish').val())
        }

        $('#image').on('change', function () {
          if ($('input#image').val() === '') {
            $('input#publish').prop('disabled', true)
            $('input#publish').val('Veuillez renseigner une image')
          } else {
            $('input#publish').prop('disabled', false)
            $('input#publish').val($('input#original_publish').val())
          }
        })
      }

      //Gestion du select : Thème principal -> Evenement
      $('#theme-principal').on('change', function () {
        cleanSoustheme()
      })
      cleanSoustheme()

      //Gestion du select : categorie public -> Evenement
      $('#categorie-public').on('change', function () {
        cleanPublic()
      })
      cleanPublic()
    }
  })
})(jQuery)