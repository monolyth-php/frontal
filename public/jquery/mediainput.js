
/**
 * Monolyth's default iframe-based fancy image uploader.
 * Includes preview of uploaded file, custom error handling and more.
 *
 * The Monolyth.media plugin defines some custom events that can be listened for
 * to define your own handling of uploads:
 * - upload_request.Monolyth: show a popup for an image file.
 * - upload_progress.Monolyth: the upload is busy, perhaps show its progress?
 * - upload_done.Monolyth: upload successful, update the preview?
 * - upload_cancel.Monolyth: user clicked cancel; cleanup.
 *
 * Further, it defines two basic methods used to handle uploads:
 * - media.upload(jQuery element). Triggers upload_request.Monolyth. This should
 *   show a form with id="monolyth_upload" with an image input.
 * - media.set(el, img). Used by the iframe to update its parent.
 *
 * It also defines some generic example methods for handling these events:
 * - media.request. Request an upload.
 * - media.progress. Upload in progress.
 * - media.done. Upload finished.
 * - media.cancel. Upload cancelled.
 * To use on of the default methods, simply bind it like so:
 * <code>
 * $(window).on('request.Monolyth', window.Monolyth.media.request);
 * </code>
 */
(function($) {
    window.Monolyth.media = window.Monolyth.media || {};
    m = window.Monolyth.media;
    m.set = function(el, img) {
        el.val(img);
        var id = el.attr('name').replace(/[\[\]]+/, '-');
        $('#' + id).removeClass('no-img');
        $(window).trigger('upload_done.Monolyth');
    };
    m.upload = function(el) {
        $(window).trigger('upload_request.Monolyth');
        $('#monolyth_upload').submit(function() {
            $(window).trigger('upload_progress.Monolyth');
            return true;
        });
        $('#monolyth_upload .close').click(function() {
            $(window).trigger('upload_cancel.Monolyth');
            return false;
        });
    };
    m.request = function(event) {
    };
    m.progress = function(event) {
    };
    m.done = function(event) {
    };
    m.cancel = function(event) {
    };
})(jQuery);

window.setImage = function(name, position) {
    var el = '[name=media';
    if (position) {
        el += '\\[' + position + '\\]';
    } else {
        position = 0;
    }
    el += '\\[media\\]]';
    $(el).val(name);
    $('#media' + position).removeClass('no-img');
    $('#upload').fadeOut(400, function() {
        $('#modal').fadeOut(400, function() { $(this).remove(); });
        $(this).remove();
    });
};
window.doUpload = function(position) {
    $('body').append('<div id="modal"/>').
    append('<form id="upload" class="box static" ' +
        'method="post" target="media' + position + '" ' +
        'action="' + $('#media' + position).attr('src') + '" ' +
        'enctype="multipart/form-data">' +
        '<header><a href="#" class="icon close">[x]</a></header>' +
        '<div><p>' + Monolyth.text.get('\\upload') + '</p>' +
        '<input type="file" name="media" required>' +
        '<button type="submit" name="act_submit">' +
        Monolyth.text.get('\\upload/submit') + '</button>' +
        '</div></form>');
    $('#upload').submit(function() {
        $('div', this).fadeOut(0);
        $(this).append('<p>' + Monolyth.text.get('\\upload/progress') + '</p>');
        return true;
    });
    $('#upload .close').click(function() {
        $('#upload').fadeOut(400, function() {
            $('#modal').fadeOut(400, function() { $(this).remove(); });
            $(this).remove();
        });
        return false;
    });
    $('#modal').fadeTo(400, .5, function() {
        $('#upload').fadeOut(0).addClass('inited').fadeIn();
    });
};

