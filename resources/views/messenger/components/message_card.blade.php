@if ($attachment)
<div class="wsus__single_chat_area message_card" data-id="{{ $id }}">
    <div class="wsus__single_chat {{ $from_id === auth()->user()->id ? 'chat_right' : "" }}">
        <a class="venobox" data-gall="gallery01" href="images/chat_img.png">
            <img src="{{ asset($attachment) }}" alt="gallery1" class="img-fluid w-100">
        </a>
        @if($message)
        <p class="messages">{{ $message }}</p>
        @endif
        <span class="time"> 5h ago</span>
        <a class="action delete_message" data-id="{{ $id }}" href="#"><i class="fas fa-trash"></i></a>
    </div>
</div>
@else    
<div class="wsus__single_chat_area message_card" data-id="{{ $id }}">
    <div class="wsus__single_chat {{ $from_id === auth()->user()->id ? 'chat_right' : "" }}">
        <p class="messages">{{ $message }}</p>
        <span class="time"> {{ timeAgo($created_at) }}</span>
        <a class="action delete_message" data-id="{{ $id }}" href="#"><i class="fas fa-trash"></i></a>
    </div>
</div>
@endif
