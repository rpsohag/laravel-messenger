<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
aria-hidden="true">
<div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-body">
            <form action="#" class="profile-form" enctype="multipart/form-data">
                @csrf
                <div class="file">
                    <img src="{{ asset(auth()->user()->avatar) }}" alt="Upload" class="img-fluid profile_image_preview">
                    <label for="select_file"><i class="fal fa-camera-alt"></i></label>
                    <input id="select_file" name="avatar" type="file" hidden>
                </div>
                <p>Edit information</p>
                <input type="text" name="name" placeholder="Enter your name (required)" value="{{ auth()->user()->name }}">
                <span id="input_name_error" class="error-message text-danger d-none"></span>
                <input type="text" name="username" placeholder="Enter your username (required)" value="{{ auth()->user()->username }}">
                <span id="input_username_error" class="error-message text-danger d-none"></span>
                <input type="email" name="email" placeholder="Enter your email (required)" value="{{ auth()->user()->email }}">
                <span id="input_email_error" class="error-message text-danger d-none"></span>
                <p>Change password</p>
                <div class="row">
                    <div class="col-xl-12">
                        <input name="current_password" type="password" placeholder="Old Password">
                        <span id="input_current_password_error" class="error-message text-danger d-none"></span>
                    </div>
                    <div class="col-xl-12">
                        <input name="password" type="password" placeholder="New Password">
                        <span id="input_password_error" class="error-message text-danger d-none"></span>
                    </div>
                    <div class="col-xl-12">
                        <input name="password_confirmation" type="password" placeholder="Confirm Password">
                        <span id="input_password_confirmation_error" class="error-message text-danger d-none"></span>
                    </div>
                </div>
                <div class="modal-footer mt-3 p-0 justify-content-end">
                    <button type="button" class="btn btn-secondary cancel"
                        data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary save" id="save_button">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

@push('scripts')
    <script>
        $(document).ready(function(){
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $(".profile-form").submit(function(e){
                e.preventDefault();
                let formData = new FormData(this);
                const saveBtn = $("#save_button");
                $(".error-message").removeClass("d-block").addClass("d-none").text('');
                $.ajax({
                    method: 'POST',
                    url: "{{ route('messenger.profile.update') }}",
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function(){
                        saveBtn.text("Saving....")
                        saveBtn.prop("disabled", true)
                    },
                    success: function(data){
                        notyf.success('Your changes have been successfully saved!');
                        saveBtn.text("Save changes")
                        saveBtn.prop("disabled", false)
                    },
                    error: function(xhr, status, error){
                        const errors = xhr.responseJSON.errors;
                        // $.each(errors, function(index, value){
                        //     console.log(value)
                        // })
                        // if(errors["name"][0]){
                        //     $("#input_name_error").removeClass("d-none").addClass("d-block").text(errors["name"][0])
                        // }
                        if (errors) {
                            $.each(errors, function(index, value){
                                $("#input_" + index + "_error").removeClass("d-none").addClass("d-block").text(value[0]);
                            });
                        }
                        saveBtn.text("Save changes");
                        saveBtn.prop("disabled", false);
                    }
                })
            })
        })    
    </script>    
@endpush