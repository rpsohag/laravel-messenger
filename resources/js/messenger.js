/**
 * Global Variables
 */

let temporaryMsgId = 0;
const messageForm = $(".message-form");
const messageInput = $(".message_input");
const messageBoxContainer = $(".wsus__chat_area_body");
const csrf_token = $("meta[name=csrf-token]").attr("content");
const getMessengerId = () => $("meta[name=id]").attr('content');
const setMessengerId = (id) => $("meta[name=id]").attr('content', id);



/**
 * -------------------------
 *  Functions
 * -------------------------
 */

function enableChatBoxLoader(){
    $('.wsus__message_paceholder').removeClass('d-none')
}

function disableChatBoxLoader(){
    $('.wsus__message_paceholder').addClass('d-none')
}


function imagePreview(input, selector){
    if(input.files && input.files[0]){
        const render = new FileReader();

        render.onload = function(e){
            $(selector).attr('src', e.target.result)
        }

        render.readAsDataURL(input.files[0])
    }
}

function messageFormReset(){
    $(".attachment_block").addClass('d-none')
    messageForm.trigger("reset")
}

function sendMessage(){
    temporaryMsgId += 1;
    let tempID = `temp_${temporaryMsgId}`
    let hasAttachment = !!$(".attachment_input").val();

    const inputValue = $(".message_input").val();


    
    if(inputValue.length > 0 || hasAttachment){
        const formData = new FormData($(".message-form")[0]);
        formData.append("id", getMessengerId())
        formData.append("temporaryMsgId", tempID)
        formData.append("_token", csrf_token)
        $.ajax({
            method: "POST",
            url: "/messenger/send-message",
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function(){
                // add temp message on dom
                if(hasAttachment){
                    messageBoxContainer.append(sendTempMessageCard(inputValue, tempID, true))
                }else{
                    messageBoxContainer.append(sendTempMessageCard(inputValue, tempID))
                }
                messageForm.trigger("reset")
                $(".emojionearea-editor").text("")
            },
            success: function(data){
                const tempMsgCardElement = messageBoxContainer.find(`.message_card[data-id=${data.tempID}]`);
                tempMsgCardElement.before(data.message)
                tempMsgCardElement.remove();
                messageFormReset();
            },
            error: function(xhr, status, errors){

            }
        })
    }

}

function sendTempMessageCard(message, tempId, attachment = false){
    if(attachment){

        return `
          <div class="wsus__single_chat_area message_card" data-id="${tempId}">
                <div class="wsus__single_chat chat_right">
                    <div class="pre_loader">
                        <div class="spinner-border text-light" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    ${message.length > 0 ? `<p class="messages">${message}</p>` : ""}
                    
                    <span class="clock"> <i class="fas fa-clock"></i> 5h ago</span>
                    <a class="action" href="#"><i class="fas fa-trash"></i></a>
                </div>
            </div>
        `
    }else{
            return `
            <div class="wsus__single_chat_area message_card" data-id="${tempId}">
            <div class="wsus__single_chat chat_right">
            <p class="messages">${message}</p>
            <span class="clock"> <i class="fas fa-clock"></i> 5h ago</span>
            <a class="action" href="#"><i class="fas fa-trash" aria-hidden="true"></i></a>
            </div>
            </div>
            `

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

function idInfo(id){
    $.ajax({
        method: 'GET',
        url: "/messenger/id-info",
        data: {
            id: id
        },
        beforeSend: function(){
            NProgress.start();
            enableChatBoxLoader()
        },
        success: function(data){
            $('.messenger_header').find("img").attr("src", import.meta.env.VITE_APP_URL + '/' + data.user.avatar)
            $('.messenger_info_view').find(".avatar").attr("src", import.meta.env.VITE_APP_URL + '/' + data.user.avatar)
            $('.messenger_header').find("h4").text(data.user.name)
            $('.messenger_info_view').find("h3").text(data.user.name)
            NProgress.done();
            disableChatBoxLoader()
        },
        error: function(){
            disableChatBoxLoader()
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

    // 
    $("body").on("click", ".messenger_list_item", function(){
        const dataId = $(this).attr("data-id");
        setMessengerId(dataId)
        idInfo(dataId);
    })

    // send message

    $(".message-form").on("submit", function(e){
        e.preventDefault();
        sendMessage()
    })

    // attachment preview
    $('.attachment_input').change(function(){
        $(".attachment_block").removeClass('d-none')
        imagePreview(this, '.attachment_preview')
    })

    // cancel attachment
    $(".cancel_attachment").on("click", function(){
        messageFormReset()
        $(".emojionearea-editor").text("")
    })
})


