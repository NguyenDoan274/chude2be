@extends('sinhvien.layout')

@section('content')
<div class="container mt-4 text-center">
    <div id="alert-container" class="mt-2"></div>
    <h3>📸 Điểm danh khuôn mặt</h3>
    <a href="{{ route('diemdanh.show', $lichThi->id) }}" class="btn btn-secondary mt-2">Trở về</a>
    <hr>

    @if ($lichThi->trang_thai === 'chua_dien_ra')
        <div class="alert alert-warning text-center">
            ⏳ Chưa đến giờ thi – không thể điểm danh ngay lúc này.
        </div>
    @elseif ($lichThi->trang_thai === 'da_ket_thuc')
        <div class="alert alert-danger text-center">
            ❌ Ca thi đã kết thúc – không thể tiếp tục điểm danh.
        </div>
    @else
        <!-- Loading overlay -->
        <div id="loadingOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-none"
            style="background: rgba(0,0,0,0.6); z-index: 9999;">
            <div class="d-flex flex-column justify-content-center align-items-center h-100">
                <div class="spinner-border text-light" style="width: 4rem; height: 4rem;"></div>
                <p class="mt-3 text-white fs-4">Đang xử lý, vui lòng chờ...</p>
            </div>
        </div>
        <div id="legendContainer" class="mb-3"></div>
        <div class="row">

            <!-- Camera + live overlay -->
            <div class="col-12 col-md-8 text-center mb-3">
                <div id="cameraContainer" class="position-relative d-none" style="display:inline-block; max-width:100%;">
                    <video id="camera" autoplay playsinline class="border rounded shadow-sm"
                        style="max-width:100%; display:block;"></video>

                    <canvas id="overlayLive"
                            class="position-absolute top-0 start-0"
                            style="pointer-events:none; left:0; top:0;"></canvas>
                </div>

                <!-- Snapshot (after chụp) -->
                <div id="snapshotContainer" class="position-relative d-none" style="display:inline-block; max-width:100%;">
                    <img id="capturedImage" class="img-fluid border rounded" style="display:block;">
                    <canvas id="overlaySnapshot" class="position-absolute top-0 start-0" style="pointer-events:none; left:0; top:0;"></canvas>
                </div>

                <div class="mt-3 d-flex flex-wrap justify-content-center gap-2">
                    <button id="btn-open-camera" class="btn btn-primary px-4 py-2">🎥 Mở Camera</button>
                    <button id="btn-shot" class="btn btn-success px-4 py-2 d-none">📸 Chụp ảnh</button>
                    <button id="btn-back" class="btn btn-secondary px-4 py-2 d-none">🔄 Chụp lại</button>
                </div>
            </div>

            <!-- Danh sách sinh viên (kết quả) -->
            <div class="col-12 col-md-4 d-none" id="studentsCol" style="max-height: 520px; overflow-y:auto;">
                <form id="confirmForm">
                    <div id="studentsList" class="list-group mb-2"></div>
                    <button type="submit" id="btn-confirm" class="btn btn-primary btn-lg w-100">
                        Xác nhận điểm danh
                    </button>
                </form>
            </div>

        </div>

        <!-- DANH SÁCH SINH VIÊN ĐÃ ĐIỂM DANH -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h4 class="mb-0">🧾 Danh sách sinh viên</h4>
                    </div>
                    <div class="card-body">
                        <!-- Thanh tìm kiếm -->
                        <form method="GET" action="{{ route('rekognition.index', $lichThi->id) }}" class="mb-3 d-flex align-items-center gap-2 flex-wrap">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                class="form-control" placeholder="🔍 Tìm MSSV hoặc tên sinh viên"
                                style="max-width: 300px;">
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="chua_diem_danh" value="1" id="chuaDiemDanh"
                                    {{ request('chua_diem_danh') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="chuaDiemDanh">Chưa điểm danh</label>
                            </div>

                            <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                            
                            @if(request()->has('search') || request()->has('chua_diem_danh'))
                                <a href="{{ route('rekognition.index', $lichThi->id) }}" class="btn btn-outline-secondary">Xóa lọc</a>
                            @endif
                        </form>

                        @if ($sinhViens->isEmpty())
                            <div class="alert alert-warning">Không có sinh viên nào.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle text-center">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Mã SV</th>
                                            <th>Họ tên</th>
                                            <th>Lớp</th>
                                            <th>Điểm danh</th>
                                            <th>Kết quả</th>
                                            <th>Độ chính xác</th>
                                            <th>Thời gian</th>
                                            <th>Hình thức</th>
                                        </tr>
                                    </thead>
                                    <tbody id="attendanceTableBody">
                                        @foreach ($sinhViens as $item)
                                            <tr data-id="{{ $item->id }}">
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->sinhVien->ma_sv }}</td>
                                                <td>{{ $item->sinhVien->ho_ten }}</td>
                                                <td>{{ $item->sinhVien->lop }}</td>
                                                <td>
                                                    <input type="checkbox" class="form-check-input toggle-diemdanh"
                                                        data-id="{{ $item->id }}" {{ $item->ket_qua === 'hợp lệ' ? 'checked' : '' }}
                                                        @if($lichThi->trang_thai === 'da_ket_thuc') disabled @endif>
                                                </td>
                                                <td class="col-ketqua">{{ $item->ket_qua ?? 'Chưa có' }}</td>
                                                <td class="col-dochinhxac">{{ $item->do_chinh_xac ?? '-' }}</td>
                                                <td class="col-thoigian">{{ $item->thoi_gian_dd ?? '-' }}</td>
                                                <td class="col-hinhthuc">{{ $item->hinh_thuc_dd ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {

    /* -------------------------
       Adaptive Config - Tự động điều chỉnh theo performance
    ------------------------- */
    let DETECT_INTERVAL_MS = 180;
    let MIN_FACE_PIXELS = 24;
    let DETECTION_CONFIDENCE = 0.45;
    
    let detectionTimes = [];
    let performanceLevel = 'high';
    let isDetecting = false;

    /* -------------------------
       Các elements và biến 
    ------------------------- */
    const video = document.getElementById('camera');
    const overlayLive = document.getElementById('overlayLive');
    const overlaySnap = document.getElementById('overlaySnapshot');
    const cameraContainer = document.getElementById('cameraContainer');
    const snapshotContainer = document.getElementById('snapshotContainer');
    const capturedImage = document.getElementById('capturedImage');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const btnOpen = document.getElementById('btn-open-camera');
    const btnShot = document.getElementById('btn-shot');
    const btnBack = document.getElementById('btn-back');
    const studentsCol = document.getElementById('studentsCol');
    const studentsList = document.getElementById('studentsList');
    const confirmForm = document.getElementById('confirmForm');
    const btnConfirm = document.getElementById('btn-confirm');
    const legendContainer = document.getElementById('legendContainer');
    const attendanceTableBody = document.getElementById('attendanceTableBody');

    let stream = null;
    let capturedBase64 = '';
    let detectedFacesLive = [];
    let detectedStudents = [];
    let detectTimer = null;
    let originalDetectedStudents = [];
    
    // BIẾN MỚI: Quản lý camera
    let currentFacingMode = 'environment'; // Ưu tiên camera sau
    let btnSwitchCamera = null;

    /* -------------------------
       Helper: rounded rect 
    ------------------------- */
    function roundRect(ctx, x, y, w, h, r) {
        ctx.beginPath();
        ctx.moveTo(x + r, y);
        ctx.lineTo(x + w - r, y);
        ctx.quadraticCurveTo(x + w, y, x + w, y + r);
        ctx.lineTo(x + w, y + h - r);
        ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
        ctx.lineTo(x + r, y + h);
        ctx.quadraticCurveTo(x, y + h, x, y + h - r);
        ctx.lineTo(x, y + r);
        ctx.quadraticCurveTo(x, y, x + r, y);
        ctx.closePath();
    }

    /* -------------------------
       Tạo nút chuyển đổi camera
    ------------------------- */
    function createSwitchCameraButton() {
        if (!btnOpen || !btnOpen.parentNode) return;
        
        btnSwitchCamera = document.createElement('button');
        btnSwitchCamera.type = 'button';
        btnSwitchCamera.className = 'btn btn-outline-secondary btn-sm';
        btnSwitchCamera.innerHTML = '🔄 Đổi Camera';
        btnSwitchCamera.style.marginLeft = '8px';
        btnSwitchCamera.classList.add('d-none'); // Ẩn ban đầu
        
        btnSwitchCamera.addEventListener('click', switchCamera);
        
        btnOpen.parentNode.appendChild(btnSwitchCamera);
    }

    /* -------------------------
       Hàm chuyển đổi camera
    ------------------------- */
    async function switchCamera() {
        if (!stream) return;
        
        // Đóng stream hiện tại
        stream.getTracks().forEach(track => track.stop());
        
        // Chuyển đổi camera
        currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
        
        console.log(`🔄 Chuyển sang camera: ${currentFacingMode === 'environment' ? 'SAU' : 'TRƯỚC'}`);
        
        // Hiển thị loading
        showCameraLoading();
        
        try {
            await openCameraStream();
            
            // Cập nhật UI
            btnShot.classList.remove('d-none');
            if (detectTimer) {
                clearInterval(detectTimer);
            }
            startLiveDetection();
            
        } catch (error) {
            console.error('Lỗi chuyển camera:', error);
            alert('Không thể chuyển camera. Vui lòng thử lại.');
        } finally {
            hideCameraLoading();
        }
    }

    /* -------------------------
       Hàm mở camera stream
    ------------------------- */
    async function openCameraStream() {
        const constraints = {
            video: { 
                width: { ideal: 640 },
                height: { ideal: 480 },
                frameRate: { ideal: 20 },
                facingMode: currentFacingMode // Sử dụng facingMode thay vì deviceId
            }
        };
        
        console.log(`📷 Đang mở camera: ${currentFacingMode === 'environment' ? 'SAU' : 'TRƯỚC'}`);
        
        try {
            stream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = stream;
            
            return new Promise((resolve, reject) => {
                video.addEventListener('playing', () => {
                    console.log('✅ Camera đã sẵn sàng');
                    resolve();
                });
                
                video.addEventListener('error', (e) => {
                    reject(new Error('Lỗi video: ' + e.message));
                });
                
                // Timeout fallback
                setTimeout(() => {
                    if (video.readyState >= 2) {
                        resolve();
                    }
                }, 2000);
            });
            
        } catch (error) {
            console.error('Lỗi mở camera với facingMode:', error);
            
            // Thử với constraints đơn giản hơn
            try {
                console.log('🔄 Thử mở camera với constraints đơn giản...');
                const fallbackConstraints = {
                    video: { 
                        facingMode: currentFacingMode
                    }
                };
                
                stream = await navigator.mediaDevices.getUserMedia(fallbackConstraints);
                video.srcObject = stream;
                
                return new Promise((resolve) => {
                    video.addEventListener('playing', resolve);
                    setTimeout(resolve, 1000);
                });
                
            } catch (fallbackError) {
                console.error('Lỗi cả 2 cách mở camera:', fallbackError);
                throw fallbackError;
            }
        }
    }

    /* -------------------------
       Load model 
    ------------------------- */
    console.log('Đang tải model face detection...');
    try {
        await Promise.all([
            faceapi.nets.ssdMobilenetv1.loadFromUri('/models')
        ]);
        console.log('Model đã tải xong!');
    } catch (error) {
        console.error('Lỗi tải model:', error);
        alert('Lỗi tải model nhận diện khuôn mặt. Vui lòng thử lại.');
        return;
    }

    /* -------------------------
       Performance Detection
    ------------------------- */
    function detectPerformanceLevel() {
        const startTime = performance.now();
        
        let result = 0;
        for (let i = 0; i < 1000000; i++) {
            result += Math.sqrt(i) * Math.sin(i);
        }
        
        const endTime = performance.now();
        const processingTime = endTime - startTime;
        
        console.log('Performance test:', processingTime + 'ms');
        
        if (processingTime < 50) {
            performanceLevel = 'high';
            DETECT_INTERVAL_MS = 180;
            MIN_FACE_PIXELS = 24;
            DETECTION_CONFIDENCE = 0.45;
        } else if (processingTime < 150) {
            performanceLevel = 'medium';
            DETECT_INTERVAL_MS = 300;
            MIN_FACE_PIXELS = 28;
            DETECTION_CONFIDENCE = 0.5;
        } else {
            performanceLevel = 'low';
            DETECT_INTERVAL_MS = 400;
            MIN_FACE_PIXELS = 32;
            DETECTION_CONFIDENCE = 0.6;
        }
        
        console.log('Performance level:', performanceLevel, 'Interval:', DETECT_INTERVAL_MS);
    }

    detectPerformanceLevel();

    /* -------------------------
       Tạo chú thích màu sắc
    ------------------------- */
    function createLegend() {
        if (legendContainer) {
            legendContainer.innerHTML = `
                <div class="d-flex flex-wrap gap-3 justify-content-center small">
                    <div class="d-flex align-items-center">
                        <div class="legend-color bg-success me-2" style="width: 12px; height: 12px; border-radius: 2px;"></div>
                        <span>Chưa điểm danh</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="legend-color bg-warning me-2" style="width: 12px; height: 12px; border-radius: 2px;"></div>
                        <span>Đã điểm danh</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="legend-color bg-danger me-2" style="width: 12px; height: 12px; border-radius: 2px;"></div>
                        <span>Không hợp lệ</span>
                    </div>
                </div>
            `;
        }
    }

    createLegend();

    /* -------------------------
       Hiển thị loading khi mở camera
    ------------------------- */
    function showCameraLoading() {
        loadingOverlay.classList.remove('d-none');
        loadingOverlay.querySelector('p').textContent = 'Đang mở camera, vui lòng chờ...';
    }

    function hideCameraLoading() {
        loadingOverlay.classList.add('d-none');
    }

    /* -------------------------
       Tạo nút chuyển đổi camera khi DOM ready
    ------------------------- */
    createSwitchCameraButton();

    /* -------------------------
       Open camera - SỬ DỤNG HÀM MỚI
    ------------------------- */
    btnOpen.addEventListener('click', async () => {
        showCameraLoading();
        
        try {
            await openCameraStream();
            
            console.log('✅ Camera đã mở thành công');
            hideCameraLoading();
            
            cameraContainer.classList.remove('d-none');
            btnShot.classList.remove('d-none');
            btnOpen.classList.add('d-none');
            
            // HIỂN THỊ NÚT CHUYỂN ĐỔI CAMERA
            if (btnSwitchCamera) {
                btnSwitchCamera.classList.remove('d-none');
            }

            // Đảm bảo canvas có kích thước đúng
            setTimeout(() => {
                if (video.videoWidth > 0 && video.videoHeight > 0) {
                    overlayLive.width = video.videoWidth;
                    overlayLive.height = video.videoHeight;
                    overlayLive.style.width = video.clientWidth + 'px';
                    overlayLive.style.height = video.clientHeight + 'px';
                    console.log('Canvas kích thước:', overlayLive.width, 'x', overlayLive.height);
                    
                    // BẮT ĐẦU DETECTION NGAY
                    startLiveDetection();
                }
            }, 100);
            
        } catch (error) {
            console.error('Lỗi mở camera:', error);
            hideCameraLoading();
            alert('Không thể mở camera. Vui lòng kiểm tra quyền truy cập camera.');
            return;
        }
    });

    /* -------------------------
       Adaptive Live Detection - GIỮ NGUYÊN
    ------------------------- */
    function startLiveDetection() {
        console.log('🚀 Bắt đầu live detection với interval:', DETECT_INTERVAL_MS);
        
        if (detectTimer) {
            clearInterval(detectTimer);
            detectTimer = null;
        }

        const ctx = overlayLive.getContext('2d');

        async function runOnce() {
            if (!video || video.paused || video.ended || video.readyState < 2 || isDetecting) {
                return;
            }
            
            if (video.videoWidth === 0 || video.videoHeight === 0) {
                console.log('Video chưa sẵn sàng, bỏ qua frame');
                return;
            }

            const detectionStartTime = performance.now();
            isDetecting = true;

            let detections = [];
            try {
                detections = await faceapi.detectAllFaces(video, 
                    new faceapi.SsdMobilenetv1Options({ 
                        minConfidence: DETECTION_CONFIDENCE
                    })
                );
                console.log('👁️ Tìm thấy', detections.length, 'khuôn mặt');
            } catch (e) {
                console.warn('Lỗi face detection:', e);
                detections = [];
            } finally {
                isDetecting = false;
            }

            detectedFacesLive = detections || [];
            
            ctx.clearRect(0, 0, overlayLive.width, overlayLive.height);

            if (detectedFacesLive.length > 0) {
                console.log('🎨 Vẽ', detectedFacesLive.length, 'viền khuôn mặt');
                
                detectedFacesLive.forEach((det, idx) => {
                    const box = det.box;
                    if ((box.width < MIN_FACE_PIXELS) || (box.height < MIN_FACE_PIXELS)) {
                        return;
                    }

                    if (performanceLevel === 'high') {
                        ctx.fillStyle = 'rgba(0,200,0,0.12)';
                        ctx.fillRect(box.x, box.y, box.width, box.height);

                        ctx.lineWidth = 3;
                        ctx.strokeStyle = 'rgba(0,255,0,0.95)';
                        roundRect(ctx, box.x, box.y, box.width, box.height, 10);
                        ctx.stroke();

                        const label = String(idx + 1);
                        ctx.font = '18px Arial';
                        const tw = ctx.measureText(label).width;
                        const pad = 8;
                        const bw = tw + pad;
                        const bh = 22;
                        ctx.fillStyle = 'rgba(0,0,0,0.7)';
                        ctx.fillRect(box.x, box.y - bh - 6, bw, bh);

                        ctx.fillStyle = 'lime';
                        ctx.fillText(label, box.x + 6, box.y - 12);
                        
                    } else if (performanceLevel === 'medium') {
                        ctx.lineWidth = 2;
                        ctx.strokeStyle = 'rgba(0,255,0,0.9)';
                        roundRect(ctx, box.x, box.y, box.width, box.height, 8);
                        ctx.stroke();

                        const label = String(idx + 1);
                        ctx.font = '16px Arial';
                        const tw = ctx.measureText(label).width;
                        const pad = 6;
                        const bw = tw + pad;
                        const bh = 20;
                        ctx.fillStyle = 'rgba(0,0,0,0.7)';
                        ctx.fillRect(box.x, box.y - bh - 4, bw, bh);

                        ctx.fillStyle = 'lime';
                        ctx.fillText(label, box.x + 3, box.y - 8);
                        
                    } else {
                        ctx.lineWidth = 2;
                        ctx.strokeStyle = 'rgba(0,255,0,0.9)';
                        ctx.strokeRect(box.x, box.y, box.width, box.height);

                        const label = String(idx + 1);
                        ctx.font = '14px Arial';
                        ctx.fillStyle = 'rgba(0,0,0,0.7)';
                        ctx.fillRect(box.x, box.y - 20, 20, 18);

                        ctx.fillStyle = 'lime';
                        ctx.fillText(label, box.x + 4, box.y - 6);
                    }
                });
            }
        }

        runOnce().catch(err => {
            console.error('Lỗi trong lần chạy đầu tiên:', err);
        });

        detectTimer = setInterval(() => {
            runOnce().catch(err => {
                console.error('Lỗi trong interval detection:', err);
            });
        }, DETECT_INTERVAL_MS);
    }

    /* -------------------------
       Capture ảnh - CẬP NHẬT ẨN NÚT CHUYỂN CAMERA
    ------------------------- */
    btnShot.addEventListener('click', async () => {
        console.log('📸 Bắt đầu chụp ảnh...');
        
        const w = video.videoWidth;
        const h = video.videoHeight;
        if (!w || !h) {
            alert('Camera chưa sẵn sàng, thử lại nhé.');
            return;
        }

        console.log('Kích thước video:', w, 'x', h);

        if (detectTimer) {
            clearInterval(detectTimer);
            detectTimer = null;
            console.log('Đã dừng live detection');
        }

        const MAX = 960;
        let newW = w, newH = h;
        if (w > h && w > MAX) {
            newW = MAX; newH = Math.round(h * (MAX / w));
        } else if (h > MAX) {
            newH = MAX; newW = Math.round(w * (MAX / h));
        }

        const canvas = document.createElement('canvas');
        canvas.width = newW;
        canvas.height = newH;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, newW, newH);

        capturedBase64 = canvas.toDataURL('image/jpeg', 0.8);
        capturedImage.src = capturedBase64;

        // Chuyển UI - ẨN NÚT CHUYỂN CAMERA
        cameraContainer.classList.add('d-none');
        snapshotContainer.classList.remove('d-none');
        btnShot.classList.add('d-none');
        btnBack.classList.remove('d-none');
        studentsCol.classList.remove('d-none');
        
        if (btnSwitchCamera) {
            btnSwitchCamera.classList.add('d-none');
        }

        console.log('Đã chụp ảnh, chuyển sang mode xem ảnh');

        capturedImage.onload = async () => {
            console.log('🖼️ Ảnh đã load, gửi lên server...');
            overlaySnap.width = capturedImage.naturalWidth;
            overlaySnap.height = capturedImage.naturalHeight;
            overlaySnap.style.width = capturedImage.clientWidth + 'px';
            overlaySnap.style.height = capturedImage.clientHeight + 'px';

            await sendToServerAndRender();
        };
    });

    /* -------------------------
       Quay lại camera - HIỆN LẠI NÚT CHUYỂN CAMERA
    ------------------------- */
    btnBack.addEventListener('click', () => {
        console.log('🔄 Quay lại camera');
        
        snapshotContainer.classList.add('d-none');
        cameraContainer.classList.remove('d-none');
        studentsList.innerHTML = '';
        studentsCol.classList.add('d-none');

        const ctx = overlaySnap.getContext('2d');
        ctx.clearRect(0, 0, overlaySnap.width, overlaySnap.height);

        // HIỆN LẠI NÚT CHUYỂN CAMERA
        if (btnSwitchCamera) {
            btnSwitchCamera.classList.remove('d-none');
        }

        startLiveDetection();

        btnBack.classList.add('d-none');
        btnShot.classList.remove('d-none');
        
        if (btnConfirm) {
            btnConfirm.disabled = false;
            btnConfirm.textContent = 'Xác nhận điểm danh';
            btnConfirm.classList.remove('btn-success', 'btn-secondary');
            btnConfirm.classList.add('btn-primary');
        }
    });

    async function sendToServerAndRender() {
        loadingOverlay.classList.remove('d-none');
        loadingOverlay.querySelector('p').textContent = 'Đang xử lý ảnh, vui lòng chờ...';

        const form = new FormData();
        form.append('hinh_anh_base64', capturedBase64);
        form.append('lich_thi_id', '{{ $lichThi->id }}');
        form.append('_token', '{{ csrf_token() }}');

        try {
            const res = await fetch("{{ route('rekognition.compareMany', ['lichThi' => $lichThi->id]) }}", {
                method: 'POST',
                body: form
            });

            if (!res.ok) {
                let text = await res.text();
                try {
                    const j = JSON.parse(text);
                    throw new Error(j.message || j.error || text);
                } catch (e) {
                    throw new Error(text || 'Lỗi server');
                }
            }

            const data = await res.json();
            detectedStudents = data.faces || [];
            originalDetectedStudents = JSON.parse(JSON.stringify(detectedStudents));

            drawSnapshotOverlay();
            renderStudentsList();
            updateConfirmButton();

        } catch (err) {
            console.error('Send image error', err);
            alert('Lỗi gửi ảnh: ' + (err.message || err));
        } finally {
            loadingOverlay.classList.add('d-none');
        }
    }

    function drawSnapshotOverlay() {
        const ctx = overlaySnap.getContext('2d');
        ctx.clearRect(0, 0, overlaySnap.width, overlaySnap.height);

        const scaleX = overlaySnap.width > 0 ? (overlaySnap.width / capturedImage.naturalWidth) : 1;
        const scaleY = overlaySnap.height > 0 ? (overlaySnap.height / capturedImage.naturalHeight) : 1;

        detectedStudents.forEach((face, idx) => {
            const box = face.box || {};
            const x = Math.round(box.x * scaleX);
            const y = Math.round(box.y * scaleY);
            const w = Math.round(box.width * scaleX);
            const h = Math.round(box.height * scaleY);

            let color;
            if (face.valid) {
                color = face.checkedIn ? '#FFD400' : '#00FF6A';
            } else {
                color = '#FF3B30';
            }

            ctx.fillStyle = (color === '#FF3B30') ? 'rgba(255,59,48,0.16)' : 
                        (color === '#FFD400' ? 'rgba(255,212,0,0.12)' : 'rgba(0,255,106,0.12)');
            ctx.fillRect(x, y, w, h);

            ctx.lineWidth = 3;
            ctx.strokeStyle = color;
            roundRect(ctx, x, y, w, h, 12);
            ctx.stroke();

            const labelIndex = String(idx + 1);
            const name = face.name || 'Unknown';
            const sim = (typeof face.similarity === 'number') ? (face.similarity.toFixed(1) + '%') : '';
            const label = `${labelIndex} ${name} ${sim}`.trim();

            ctx.font = '16px Arial';
            const tw = ctx.measureText(label).width;
            const pad = 8;
            const bw = tw + pad;
            const bh = 22;
            ctx.fillStyle = 'rgba(0,0,0,0.65)';
            ctx.fillRect(x, y - bh - 6, bw, bh);

            ctx.fillStyle = color;
            ctx.fillText(label, x + 6, y - 10);
        });
    }

    function renderStudentsList() {
    studentsList.innerHTML = '';
    
    let hasValidStudents = false;

    detectedStudents.forEach((f, i) => {
        let badgeClass = '';
        let statusText = '';
        let isDisabled = true;
        
        if (f.valid) {
            if (f.checkedIn) {
                badgeClass = 'bg-warning';
                statusText = 'Đã điểm danh';
            } else {
                badgeClass = 'bg-success';
                statusText = 'Chưa điểm danh';
                hasValidStudents = true;
                isDisabled = false;
            }
        } else {
            badgeClass = 'bg-danger';
            statusText = 'Không hợp lệ';
        }

        const simText = (typeof f.similarity === 'number') ? `${f.similarity.toFixed(1)}%` : 'N/A';
        
        // HIỂN THỊ THÔNG TIN SINH VIÊN
        const maSV = f.name || 'Không xác định';
        const hoTen = f.ho_ten || '';
        
        // FORMAT TÊN VIẾT TẮT TRONG JAVASCRIPT
        const formatVietnameseNameShort = (fullName) => {
            if (!fullName) return '';
            
            const words = fullName.trim().split(' ');
            if (words.length <= 1) return fullName;
            
            let formatted = '';
            for (let i = 0; i < words.length - 1; i++) {
                if (words[i].length > 0) {
                    formatted += words[i].charAt(0) + '. ';
                }
            }
            
            return formatted + words[words.length - 1];
        };
        
        const hoTenVietTat = formatVietnameseNameShort(hoTen);
        
        const listItem = `
            <label class="list-group-item d-flex justify-content-between align-items-start ${isDisabled ? 'opacity-75' : ''}">
                <div class="ms-2 me-auto" style="flex: 1;">
                    <div class="d-flex align-items-center">
                        <input type="checkbox" name="faces[]" value="${i}" 
                            ${!isDisabled ? 'checked' : ''} 
                            ${isDisabled ? 'disabled' : ''} 
                            class="form-check-input me-3">
                        <div>
                            <div class="fw-bold mb-1">${maSV}</div>
                            ${hoTenVietTat ? `<div class="text-muted small mb-1">${hoTenVietTat}</div>` : ''}
                            <div class="small">
                                <span class="badge bg-info">#${i+1}</span>
                                <span class="ms-2 text-muted">Độ chính xác: ${simText}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <span class="badge ${badgeClass} align-self-center">${statusText}</span>
            </label>
        `;
        studentsList.innerHTML += listItem;
    });
    
    updateConfirmButton();
}

    function updateConfirmButton() {
        if (!btnConfirm) return;

        const validStudents = detectedStudents.filter(f => f.valid && !f.checkedIn);
        const hasSelected = document.querySelectorAll('input[name="faces[]"]:checked').length > 0;

        if (validStudents.length === 0) {
            btnConfirm.disabled = true;
            btnConfirm.textContent = 'Không còn sinh viên để điểm danh';
            btnConfirm.classList.add('btn-secondary');
            btnConfirm.classList.remove('btn-primary');
        } else if (!hasSelected) {
            btnConfirm.disabled = true;
            btnConfirm.textContent = 'Chọn sinh viên để điểm danh';
            btnConfirm.classList.add('btn-secondary');
            btnConfirm.classList.remove('btn-primary');
        } else {
            btnConfirm.disabled = false;
            btnConfirm.textContent = `Xác nhận điểm danh`;
            btnConfirm.classList.remove('btn-secondary');
            btnConfirm.classList.add('btn-primary');
        }
    }

    confirmForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const selected = [...document.querySelectorAll('input[name="faces[]"]:checked')]
        .map(cb => parseInt(cb.value));

    if (selected.length === 0) {
        alert('Vui lòng chọn ít nhất một sinh viên để điểm danh');
        return;
    }

    loadingOverlay.classList.remove('d-none');
    loadingOverlay.querySelector('p').textContent = 'Đang điểm danh, vui lòng chờ...';

    try {
        const form = new FormData();
        form.append('faces', JSON.stringify(selected));
        form.append('hinh_anh_base64', capturedBase64);
        form.append('lich_thi_id', '{{ $lichThi->id }}');
        form.append('_token', '{{ csrf_token() }}');

        const res = await fetch("{{ route('rekognition.confirmMany', ['lichThi' => $lichThi->id]) }}", {
            method: 'POST',
            body: form,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const json = await res.json();
        
        if (res.ok) {
            if (json.updated_faces) {
                detectedStudents = json.updated_faces;
            } else {
                const message = json.message || '';
                const messageLines = message.split('\n');
                
                messageLines.forEach(line => {
                    if (line.includes('🎉')) {
                        const match = line.match(/sinh viên (\S+)/);
                        if (match) {
                            const studentCode = match[1];
                            detectedStudents.forEach((student, index) => {
                                if (student.name === studentCode && selected.includes(index)) {
                                    student.checkedIn = true;
                                }
                            });
                        }
                    } else if (line.includes('không thuộc phòng thi')) {
                        const match = line.match(/Sinh viên (\S+)/);
                        if (match) {
                            const studentCode = match[1];
                            detectedStudents.forEach((student, index) => {
                                if (student.name === studentCode && selected.includes(index)) {
                                    student.valid = false;
                                }
                            });
                        }
                    }
                });
            }

            renderStudentsList();
            drawSnapshotOverlay();
            updateConfirmButton();

            // CẬP NHẬT DANH SÁCH SINH VIÊN BÊN DƯỚI MÀ KHÔNG CẦN RELOAD
            await refreshAttendanceTable();

            alert(`${json.message}`);

        } else {
            alert('Lỗi: ' + (json.message || 'Có lỗi xảy ra khi điểm danh'));
        }

    } catch (err) {
        alert('Lỗi xác nhận: ' + (err.message || err));
    } finally {
        loadingOverlay.classList.add('d-none');
    }
});

    /* -------------------------
    Cập nhật danh sách điểm danh bằng AJAX
    ------------------------- */

    async function refreshAttendanceTable() {
        console.log('🔄 Bắt đầu cập nhật danh sách...');
        
        try {
            const searchParams = new URLSearchParams(window.location.search);
            const url = `{{ route('rekognition.getAttendanceData', $lichThi->id) }}?${searchParams.toString()}`;
            
            console.log('📡 Gửi request đến:', url);
            
            const res = await fetch(url);
            
            console.log('📨 Response status:', res.status, res.ok);
            
            if (res.ok) {
                const html = await res.text();
                console.log('📄 HTML nhận được:', html);
                
                // Cập nhật trực tiếp
                attendanceTableBody.innerHTML = html;
                
                console.log('✅ Đã cập nhật trực tiếp HTML vào table body');
                console.log('🔍 Số lượng tr sau khi cập nhật:', attendanceTableBody.querySelectorAll('tr').length);
                
                // Gắn lại event listeners cho các checkbox mới
                attachToggleEventListeners();
                
                console.log('✅ Đã hoàn thành cập nhật danh sách điểm danh');
                
            } else {
                console.error('❌ Lỗi khi tải dữ liệu mới:', res.status);
            }
        } catch (error) {
            console.error('❌ Lỗi cập nhật danh sách:', error);
        }
    }
    /* -------------------------
        Gắn event listeners cho checkbox điểm danh thủ công
    ------------------------- */
    function attachToggleEventListeners() {
        document.querySelectorAll('.toggle-diemdanh').forEach(cb => {
            // Xóa event listener cũ nếu có
            cb.replaceWith(cb.cloneNode(true));
        });

        // Gắn event listener mới
        document.querySelectorAll('.toggle-diemdanh').forEach(cb => {
            cb.addEventListener('change', async function() {
                const row = this.closest('tr');
                const id = row.dataset.id;
                const checked = this.checked;
                const token = '{{ csrf_token() }}';
                row.style.opacity = "0.6";

                const showAlert = (message, type='success') => {
                    const alertBox = document.createElement('div');
                    alertBox.className = `alert alert-${type} alert-dismissible fade show`;
                    alertBox.innerHTML = `<strong>${type==='success'?'✅':'⚠️'}</strong> ${message}`;
                    const container = document.getElementById('alert-container');
                    if (container) {
                        container.innerHTML = '';
                        container.appendChild(alertBox);
                        setTimeout(() => alertBox.classList.remove('show'), 3000);
                    }
                };

                if(!checked){
                    if(!confirm("Bạn có chắc muốn hủy điểm danh sinh viên này?")){
                        this.checked = true;
                        row.style.opacity="1";
                        return;
                    }
                }

                try {
                    const res = await fetch("{{ route('diemdanh.toggle') }}", {
                        method: "POST",
                        headers: { "Content-Type":"application/json", "X-CSRF-TOKEN": token },
                        body: JSON.stringify({ id, checked })
                    });
                    const data = await res.json();
                    if(data.success){
                        const now = new Date();
                        const dateStr = now.toLocaleDateString('vi-VN');
                        const timeStrOnly = now.toLocaleTimeString('vi-VN');
                        const timeStr = `${dateStr} ${timeStrOnly}`;

                        row.querySelector('.col-ketqua').textContent = checked ? 'hợp lệ':'Chưa có';
                        row.querySelector('.col-dochinhxac').textContent = checked ? '100':'-';
                        row.querySelector('.col-thoigian').textContent = checked ? timeStr:'-';
                        row.querySelector('.col-hinhthuc').textContent = checked ? 'Thủ công' : '-';

                        row.style.transition = 'background-color 0.4s';
                        row.style.backgroundColor = checked ? '#d1e7dd' : '#f8d7da';
                        setTimeout(()=>row.style.backgroundColor='', 1000);

                        showAlert(data.message, checked?'success':'danger');
                    } else {
                        showAlert(data.message || 'Có lỗi xảy ra!', 'danger');
                        this.checked = !checked;
                    }
                } catch(e){
                    showAlert('Lỗi mạng: '+e.message,'danger');
                    this.checked = !checked;
                } finally { 
                    row.style.opacity="1"; 
                }
            });
        });
    }
    // Gắn event listeners khi trang load
    attachToggleEventListeners();

});
</script>

@endsection