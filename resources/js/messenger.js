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

/**
 * Search User
 */

let searchPage = 1;
let noMoreData = false;
let searchTemporaryValue = "";
let setSearchLoader = false;
function searchUsers(query){
    if(query != searchTemporaryValue){
         searchPage = 1;
         noMoreData = false;
    }

    searchTemporaryValue = query
    if(!noMoreData && !setSearchLoader){
        $.ajax({
            method: 'GET',
            url: "/messenger/search",
            data: { query: query, page: searchPage},
            beforeSend: function(){

                let loader = `<div class="text-center search-loader">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>`
                $('.user_search_list_result').append(loader)
            },
            success: function(data){
                setSearchLoader = false;
                $('.user_search_list_result').find('.search-loader').remove();
                if(searchPage < 2){
                    $('.user_search_list_result').html(data.results)
                }else{
                    $('.user_search_list_result').append(data.results)
                }
                noMoreData = searchPage > data?.last_page
                if(!noMoreData) searchPage += 1
            },
            error: function(xhr, status, error){
                setSearchLoader = false
            }
        })
    }
}

function debounce(callback, delay){
    let timerId;
    return function(...args){
        clearTimeout(timerId)
        timerId = setTimeout(() => {
            callback.apply(this, args)
        }, delay)
    }
}


function actionOnScroll(selector, callback, topScroll = false){
    $(selector).on('scroll', function(){
        let element = $(this).get(0);
        const condition = topScroll ? element.scrollTop == 0 : element.scrollTop + element.clientHeight >= element.scrollHeight;
        if(condition){
            callback();
        }
    })
}

$(document).ready(function(){
    $('#select_file').change(function(){
        imagePreview(this, '.profile_image_preview')
    })

    const debounceSearch = debounce(function(){
        const value = $('.user_search').val();
        searchUsers(value)
    }, 500)

    $('.user_search').on('keyup', function(){
        let query = $(this).val();
        if(query.length > 0){
            debounceSearch()
        }
    })

    // search pagination

    actionOnScroll(".user_search_list_result", function(){
        let value = $('.user_search').val();
        searchUsers(value);
    })
})

