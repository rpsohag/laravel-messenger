@if ($attachment)
<div class="wsus__single_chat_area message_card" data-id="{{ $id }}">
    <div class="wsus__single_chat {{ $from_id === auth()->user()->id ? 'chat_right' : "" }}">
            <img src="{{ asset($attachment) }}" id="messageImg{{ $id }}" alt="message" class="img-fluid messageImg">
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
