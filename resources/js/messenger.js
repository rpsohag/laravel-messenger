/**
 * Global Variables
 */

let temporaryMsgId = 0;
const messageForm = $(".message-form");
const messageInput = $(".message_input");
const messageBoxContainer = $(".wsus__chat_area_body");
const messengerContactBox = $(".messenger_contacts");
const csrf_token = $("meta[name=csrf-token]").attr("content");
const auth_id = $("meta[name=auth_id]").attr("content");
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
    $(".wsus__chat_app").removeClass("show_info")
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

// scroll to bottom
function scrollToBottom(container){
    $(container).stop().animate({
        scrollTop : $(container)[0].scrollHeight
    })
}

// update contact item

function updateContactItem(user_id){
    if(user_id != auth_id){
        $.ajax({
            method: "GET",
            url: "/messenger/update-contacts",
            data: {
                user_id: user_id
            },
            success: function(data){
                messengerContactBox.find(`.messenger_list_item[data-id="${user_id}"]`).remove()
                messengerContactBox.prepend(data.contact_item)
                if(user_id == getMessengerId()) updateSelectedContent(user_id)
                },
            error: function(xhr, status, errors){
                
            }
        })
    }
}

function updateSelectedContent(user_id){
    $("body").find('.messenger_list_item').removeClass('active')
    $("body").find(`.messenger_list_item[data-id="${user_id}"]`).addClass('active')
}

// make message seen

function makeSeen(status){
    $(`.messenger_item_list[data-id="${getMessengerId()}"]`).find(".unseen_count").remove()
    $.ajax({
        method: "POST",
        url: "/messenger/make-seen",
        data: {
            _token: csrf_token,
            id: getMessengerId()
        },
        success: function(data){

        },
        error: function(xhr, status, errors){

        }
    })
}

// delete message

function deleteMessage(message_id, ){
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            method: "DELETE",
            url: "/messenger/delete-message",
            data: {
                _token : csrf_token,
                message_id : message_id
            },
            beforeSend: function(){
                $(`.message_card[data-id="${message_id}"]`).remove()
            },
            success: function(data){
                updateContactItem(getMessengerId())
            },
            error: function(xhr, status, errors){

            }
          })
        }
      });
}

// add to favourite

function star(user_id){
    $(".favourite").toggleClass("active")
    $.ajax({
        method: "POST",
        url: "/messenger/favourite",
        data: {
            _token: csrf_token,
            id: user_id
        },
        success: function(data){
            if(data.status == 'added'){
                notyf.success("Added to favourite list")
            }else{
                notyf.success('Removed from favourite list');
            }
        },
        error: function(xhr, status, errors){

        }
    })
}


/**
 * Get messages from database 
 */

let messagePage = 1;
let noMoreMessage = false;
let messageLoading = false;
function fetchMessages(id, newFetch = false){
    if(newFetch){
        messagePage = 1;
        noMoreMessage = false;
    }
    if(!noMoreMessage && !messageLoading){
        $.ajax({
            method: "GET",
            url: "/messenger/fetch-message",
            data: {
                "_token" : csrf_token,
                "id" : id,
                "page" : messagePage
            },
            beforeSend: function(){
                messageLoading = true
                let loader = `<div class="text-center message_loader">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>`
                messageBoxContainer.prepend(loader)
            },
            success: function(data){
                messageLoading = false
                messageBoxContainer.find(".message_loader").remove()
                makeSeen(true)
                if(messagePage == 1){
                    messageBoxContainer.html(data.messages)
                    scrollToBottom(messageBoxContainer)
                }else{

                    const lastMessage = messageBoxContainer.find(".message_card").first();
                    const currentOffset = lastMessage.offset().top - messageBoxContainer.scrollTop()

                    messageBoxContainer.prepend(data.messages)
                    messageBoxContainer.scrollTop(lastMessage.offset().top - currentOffset)
                }
                // pagination
                noMoreMessage = noMoreMessage >= data.last_page
                if(!noMoreMessage){
                    messagePage += 1
                }

                disableChatBoxLoader()
            },
            error: function(xhr, status, errors){
                console.log(errors)
            }
        })
    }
}

/**
 * Get contacts from db
 */

let contactPage = 1;
let noMoreContacts = false;
let contactLoading = false;

function getContacts(){
    if(!contactLoading && !noMoreContacts){
        $.ajax({
            method: "GET",
            url: "/messenger/fetch-contacts",
            data: {
                page: contactPage
            },
            beforeSend: function(){
                contactLoading = true;
                let loader = `<div class="text-center contact_loader">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>`
            messengerContactBox.append(loader)
            },
            success: function(data){
                console.log(data)
                contactLoading = false
                messengerContactBox.find(".contact_loader").remove()
                if(contactPage < 2){
                    messengerContactBox.html(data.contacts)
                }else{
                    messengerContactBox.append(data.contacts)
                }
                noMoreContacts = contactPage >= data?.last_page
                if(!noMoreContacts) contactPage += 1
            },
            error: function(xhr, status, errors){
                contactLoading = false
                messengerContactBox.find(".contact_loader").remove()
            }
        })
    }
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
                scrollToBottom(messageBoxContainer)
                messageForm.trigger("reset")
                $(".emojionearea-editor").text("")
            },
            success: function(data){
                // update contact item
                updateContactItem(getMessengerId())

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
            fetchMessages(data.user.id, true)
            $(".wsus__chat_info_gallery").html("")

            if(data.sharedAttachments){
                $(".nothing_share").addClass('d-none')
                $(".wsus__chat_info_gallery").html(data.sharedAttachments)
            }else{
                $(".nothing_share").removeClass('d-none')
            }

            data.favourite > 0 ? $('.favourite').addClass('active') : $('.favourite').removeClass('active')
            $('.messenger_header').find("img").attr("src", import.meta.env.VITE_APP_URL + '/' + data.user.avatar)
            $('.messenger_info_view').find(".avatar").attr("src", import.meta.env.VITE_APP_URL + '/' + data.user.avatar)
            $('.messenger_header').find("h4").text(data.user.name)
            $('.messenger_info_view').find("h3").text(data.user.name)
            NProgress.done();
            
        },
        error: function(){
            disableChatBoxLoader()
        }
    })
}






$(document).ready(function(){

    if(window.innerWidth < 768){
        $("body").on("click", ".messenger_list_item", function(){
            $(".wsus__user_list").addClass("d-none")
        })
        $("body").on("click", ".back_to_list", function(){
            $(".wsus__user_list").removeClass("d-none")
        })
    }

    getContacts();

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
        updateSelectedContent(dataId)
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

    // message pagination
    actionOnScroll(".wsus__chat_area_body", function(){
        fetchMessages(getMessengerId())
    }, true)

    // contacts pagination
    actionOnScroll(".messenger_contacts", function(){
        getContacts();
    })

    $(".favourite").on("click", function(e){
        e.preventDefault()
        star(getMessengerId())
    });
    $("body").on("click", ".delete_message", function(e){
        e.preventDefault()
        const message_id = $(this).data("id")
        deleteMessage(message_id)
    });
})


