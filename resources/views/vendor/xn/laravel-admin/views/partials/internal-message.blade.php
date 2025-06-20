<template id="t-message">
    <li>
        <a href="#" data-toggle="modal" data-target="#modal-default" class="modal-info">
        <div class="pull-left">
            <span class="far fa-bell"></span>
            {{-- <img src="dist/img/user2-160x160.jpg" onerror="this.src='/vendor/laravel-admin/AdminLTE/dist/img/user2-160x160.jpg'" class="img-circle" alt="User Image"> --}}
        </div>
        <h4>
            <span class="message-title"></span>
            <small><i class="far fa-clock"></i><span class="message-timestamp"></span></small>
        </h4>
        <p class="message-content"></p>
        </a>
    </li>
</template>
<li class="dropdown messages-menu">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
      <i class="far fa-envelope"></i>
      <span class="label label-success message-count">0</span>
    </a>
    <ul class="dropdown-menu">
      <li class="header">You have <span class="message-count">0</span> messages</li>
      <li>
        <!-- inner menu: contains the actual data -->
        <ul class="menu internal-message">
            {{--  --}}
        </ul>
      </li>
      <li class="footer"><a href="#">See All Messages</a></li>
    </ul>
</li>


<div class="modal fade" id="modal-default" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="modal-title">Default Modal</h4>
            </div>
            <div class="modal-body">
                <p>One fine body…</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">已讀</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<script>
    $('.internal-message').on('click', 'a', function(){
        var title =  $(this).attr('data-title'),
            message = $(this).attr('data-message')

        $('.modal .modal-title').text(title);
        $('.modal .modal-body').text(message);
    });
</script>
