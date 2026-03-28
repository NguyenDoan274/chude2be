@extends('layouts.main-layout')

@section('content')
<div class="container mt-4">
    <h3>📤 Upload & Train khuôn mặt sinh viên</h3>
    <hr>

    <div class="mb-3">
        <label>Chọn ảnh khuôn mặt (tên file = MSSV, ≤5MB, JPG/PNG)</label>
        <input type="file" id="images" class="form-control" accept="image/jpeg,image/png" multiple required>
        <small id="fileError" class="text-danger"></small>
    </div>

    <button class="btn btn-primary" id="uploadBtn">🚀 Upload & Train</button>
    <button class="btn btn-warning ms-2" id="retrainBtn">🔁 Train lại</button>

    <hr>

    <div id="logBox" class="mt-3" style="max-height:300px; overflow-y:auto; background:#f8f9fa; padding:10px; border-radius:5px;">
        <b>📄 Log:</b>
        <div id="logs"></div>
    </div>
</div>

<script>
async function uploadTrain(url) {
    const files = document.getElementById('images').files;
    const logBox = document.getElementById('logs');

    if (files.length === 0) {
        alert("⚠️ Vui lòng chọn ảnh!");
        return;
    }

    logBox.innerHTML = "";

    let total = files.length;

    let promises = Array.from(files).map((file, index) => {
        let stt = index + 1;
        let ma_sv = file.name.substring(0, file.name.lastIndexOf('.')).trim().toUpperCase();

        let formData = new FormData();
        formData.append('ma_sv', ma_sv);
        formData.append('hinh_anh', file);

        logBox.innerHTML += `
            <div id="log-${index}">
                ▶️ [${stt}/${total}] Đang xử lý ${file.name} ...
            </div>
        `;

        return fetch(url, {
            method: "POST",
            headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: formData
        })
        .then(res => res.json())
        .then(result => {
            const logEl = document.getElementById(`log-${index}`);
            if (result.success) {
                logEl.innerHTML = `✅ [${stt}/${total}] ${file.name}: ${result.message}`;
                logEl.classList.add('text-success');
            } else {
                logEl.innerHTML = `❌ [${stt}/${total}] ${file.name}: ${result.message}`;
                logEl.classList.add('text-danger');
            }
        })
        .catch(() => {
            const logEl = document.getElementById(`log-${index}`);
            logEl.innerHTML = `🔥 [${stt}/${total}] ${file.name}: Lỗi server`;
            logEl.classList.add('text-danger');
        });
    });

    await Promise.all(promises);
    logBox.innerHTML += `<div><b>Hoàn tất ${total} ảnh!</b></div>`;
}
// 🚀 Train lần đầu
document.getElementById('uploadBtn').onclick = () =>
    uploadTrain("{{ route('rekognition.train.ajax') }}");

// 🔁 Train lại
document.getElementById('retrainBtn').onclick = () =>
    uploadTrain("{{ route('rekognition.retrain.ajax') }}");
</script>

<script>
document.getElementById('images').addEventListener('change', function () {
    const maxSize = 5 * 1024 * 1024;
    const allowedTypes = ['image/jpeg', 'image/png'];

    const dt = new DataTransfer();
    const errors = [];

    for (const file of this.files) {
        if (!allowedTypes.includes(file.type)) {
            errors.push(`❌ ${file.name}: sai định dạng`);
            continue;
        }

        if (file.size > maxSize) {
            errors.push(`❌ ${file.name}: vượt quá 5MB`);
            continue;
        }

        dt.items.add(file);
    }

    this.files = dt.files;

    document.getElementById('fileError').innerHTML =
        errors.length ? errors.join('<br>') : '';
});
</script>
@endsection