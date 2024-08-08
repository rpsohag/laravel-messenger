/**
 * -------------------------
 *  Functions
 * -------------------------
 */


function imagePreview(input, selector){
    if(input.files && input.files[0]){
        const render = new FileReader();

        render.onload = function(e){
            $(selector).attr('src', e.target.result)
        }

        render.readAsDataURL(input.files[0])
    }
}


$(document).ready(function(){
    $('#select_file').change(function(){
        imagePreview(this, '.profile_image_preview')
    })
})