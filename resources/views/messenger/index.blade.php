@extends('messenger.layouts.app')
@section('messenger_content')
<section class="wsus__chat_app show_info" >

   @include('messenger.layouts.user_list')
   

    <div class="wsus__chat_area">

        <div class="wsus__message_paceholder d-none"></div>
        <div class="wsus__message_paceholder blank d-flex justify-content-center align-items-center">
            <p class="nothing_share p-3">Select a user to start conversation!</p>
        </div>

        <div class="wsus__chat_area_header">
            <div class="header_left messenger_header">
                <span class="back_to_list">
                    <i class="fas fa-arrow-left"></i>
                </span>
                <img src="{{ asset('frontend/images/author_img_2.jpg') }}" alt="User" class="img-fluid">
                <h4>Jubaydul islam</h4>
            </div>
            <div class="header_right">
                <a href="#" class="favourite"><i class="fas fa-star"></i></a>
                <a href="#" class="go_home"><i class="fas fa-home"></i></a>
                <a href="#" class="info"><i class="fas fa-info-circle"></i></a>
            </div>
        </div>

        <div class="wsus__chat_area_body">
            <div class="wsus__single_chat_area">
                <div class="wsus__single_chat chat_right">
                    <p class="messages">I'm fine, What about you ?</p>
                    <span class="time"> 5h ago</span>
                    <a class="action" href="#"><i class="fas fa-trash"></i></a>
                </div>
            </div>

            <div class="wsus__single_chat_area">
                <div class="wsus__single_chat">
                    <p class="messages">I'm so so</p>
                    <span class="time"> 5h ago</span>
                    <a class="action" href="#"><i class="fas fa-trash"></i></a>
                </div>
            </div>
        </div>
        <div class="wsus__chat_area_footer">
            <div class="footer_message">
                 <div class="img d-none attachment_block">
                    <img src="" alt="User" class="img-fluid attachment_preview">
                    <span><i class="far fa-times cancel_attachment"></i></span>
                </div>
                <form action="#" class="message-form" enctype="multipart/form-data">
                    <div class="file">
                        <label for="file"><i class="far fa-plus attachment_label"></i></label>
                        <input id="file" type="file" name="attachment" hidden class="attachment_input">
                    </div>
                    <textarea id="example1" name="message" class="message_input" rows="1" placeholder="Type a message.."></textarea>
                    <button type="submit"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>
    </div>

    @include('messenger.layouts.chat_info')

</section>
@endsection