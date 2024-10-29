<div class="min-h-screen bg-[#212A2E] py-8">
    <div class="container mx-auto px-4 max-w-3xl">
        <!-- Card Utama -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Header Section dengan border putih sebagai pembatas -->
            <div class="bg-[#212A2E] p-6 border-2 border-white rounded-t-2xl"> <!-- Modifikasi di sini -->
                <div class="text-center mb-4">
                    <h1 class="text-2xl font-bold text-yellow-400">{{ $schedule->office->nama }}</h1>
                </div>
                
                <!-- Profile Section -->
                <div class="flex items-center justify-center space-x-4 mb-6">
                    <div class="w-16 h-16 rounded-full overflow-hidden">
                        <img src="{{ Auth::user()->getImageUrl() }}" alt="Profile" class="w-full h-full object-cover">
                    </div>
                    <div class="text-white">
                        <h2 class="text-lg font-semibold">{{ Auth::user()->name }}</h2>
                        <p class="text-yellow-400">{{ Auth::user()->job_position }}</p>
                    </div>
                </div>

                <!-- Date & Time Section -->
                <div class="text-center text-white">
                    <p class="text-lg">{{ now()->format('l, d F Y') }}</p>
                </div>
            </div>

            @if (!$showPhotoUploadPage)
                <!-- Check In/Out Times -->
                <div class="grid grid-cols-2 gap-4 p-6 bg-white">
                    <div class="text-center p-4 rounded-xl border-2 border-[#212A2E]">
                        <p class="text-sm text-gray-600 mb-1">CHECK IN</p>
                        <p class="text-xl font-bold text-[#212A2E]">
                            {{$attendance ? $attendance->waktu_datang : '-'}}
                        </p>
                    </div>
                    <div class="text-center p-4 rounded-xl border-2 border-[#212A2E]">
                        <p class="text-sm text-gray-600 mb-1">CHECK OUT</p>
                        <p class="text-xl font-bold text-[#212A2E]">
                            {{$attendance && $attendance->waktu_pulang ? $attendance->waktu_pulang : '-'}}
                        </p>
                    </div>
                </div>

                <!-- Map Section -->
                <div class="p-6 bg-gray-50">
                    <div id="map" class="w-full h-[300px] rounded-xl border-2 border-gray-200 mb-4" wire:ignore></div>

                    <!-- Alert Messages -->
                    @if (session()->has('message'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                            {{ session('message') }}
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex flex-col gap-3">
                        <button onclick="tagLocation()" 
                            class="w-full py-3 bg-yellow-400 hover:bg-yellow-500 text-[#212A2E] font-bold rounded-xl transition duration-300">
                            Ambil Lokasi
                        </button>
                        @if ($insideRadius)
                            <button wire:click="initiateAttendance"
                                class="w-full py-3 bg-[#212A2E] hover:bg-gray-800 text-white font-bold rounded-xl transition duration-300">
                                Lanjut ke Foto
                            </button>
                        @endif
                    </div>
                </div>
            @else
                <!-- Photo Upload Section - Menggunakan header yang sama -->
                <div class="p-6">
                    <div x-data="cameraHandler()" x-init="initializeCamera">
                        <div class="relative mb-4">
                            <video x-show="!$wire.photoTaken" x-ref="video" class="w-full rounded-xl" autoplay playsinline></video>
                            <img x-show="$wire.photoTaken" :src="$wire.photoPreview" class="w-full rounded-xl">
                            
                            <!-- Face Detection Status -->
                            <div x-show="!$wire.photoTaken" 
                                x-text="faceDetected ? 'Wajah Terdeteksi' : 'Mendeteksi Wajah...'"
                                :class="faceDetected ? 'bg-green-500' : 'bg-yellow-400'"
                                class="absolute top-4 right-4 px-4 py-2 rounded-full text-white font-bold">
                            </div>
                        </div>

                        <!-- Photo Controls -->
                        <div class="space-y-4">
                            <button x-show="!$wire.photoTaken && faceDetected" @click="capturePhoto"
                                class="w-full py-3 bg-yellow-400 hover:bg-yellow-500 text-[#212A2E] font-bold rounded-xl">
                                Ambil Foto
                            </button>
                            
                            <button x-show="$wire.photoTaken" @click="retakePhoto"
                                class="w-full py-3 bg-gray-400 hover:bg-gray-500 text-white font-bold rounded-xl">
                                Ambil Ulang
                            </button>

                            <!-- Logbook Section -->
                            @if ($isClockOut && $photoTaken)
                                <div class="mt-4">
                                    <textarea wire:model="logbook"
                                        class="w-full p-3 border rounded-xl focus:ring-2-yellow-400"
                                        placeholder="Deskripsikan pekerjaan hari ini..."></textarea>
                                </div>
                            @endif

                            <!-- Submit Button -->
                            @if ($photoTaken)
                                <button @click="submitPresensi"
                                    class="w-full py-3 bg-[#212A2E] hover:bg-gray-800 text-white font-bold rounded-xl">
                                    Submit Presensi
                                </button>
                            @endif

                            <!-- Back Button -->
                            <button wire:click="backToMap"
                                class="w-full py-3 bg-gray-200 hover:bg-gray-300 text-[#212A2E] font-bold rounded-xl">
                                Kembali
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <!-- Add face-api.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

    <script>
        // DOM
        let map;
        let lat;
        let lng;
        let component;
        let marker;
        const office = [{{$schedule->office->latitude}}, {{$schedule->office->longitude}}];
        const radius = {{$schedule->office->radius}};

        document.addEventListener('livewire:initialized', function () {
            component = @this;
            // add map layer
            map = L.map('map').setView([{{$schedule->office->latitude}}, {{$schedule->office->longitude}}], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

            // Add office marker
            L.marker(office).addTo(map)
                .bindPopup('Office Location')
                .openPopup();

            // Add radius circle
            const circle = L.circle(office, {
                color: 'blue',
                fillColor: '#30cdf0',
                fillOpacity: 0.2,
                radius: radius,
            }).addTo(map);
        })

        // Capture user location
        function tagLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    lat = position.coords.latitude;
                    lng = position.coords.longitude;

                    if (marker) {
                        map.removeLayer(marker);
                    }

                    marker = L.marker([lat, lng]).addTo(map)
                        .bindPopup('Your Location')
                        .openPopup();
                    map.setView([lat, lng], 15);

                    // Check if within radius
                    if (radiusDistance(lat, lng, office, radius)) {
                        component.set('insideRadius', true);
                        component.set('latitude', lat);
                        component.set('longitude', lng);
                    }
                })
            } else {
                alert('Tidak bisa mendapatkan lokasi');
            }
        }

        // Calculate user radius
        function radiusDistance(lat, lng, center, radius) {
            const is_wfa = {{$schedule->is_wfa ? 'true' : 'false'}};
            if (is_wfa) {
                alert('Anda sedang WFA. Presensi dapat dilakukan dari mana saja.');
                return true;
            } else {
                let distance = map.distance([lat, lng], center);
                return distance <= radius;
            }
        }

        function cameraHandler() {
            return {
                stream: null,
                faceDetected: false,
                faceDetectionInterval: null,

                async initializeCamera() {
                    // Load only the necessary face detection model
                    await faceapi.nets.tinyFaceDetector.loadFromUri('/models');

                    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                        try {
                            this.stream = await navigator.mediaDevices.getUserMedia({ video: true });
                            this.$refs.video.srcObject = this.stream;
                            
                            // Start face detection after camera is initialized
                            this.$refs.video.addEventListener('play', () => {
                                this.startFaceDetection();
                            });
                        } catch (error) {
                            console.error("Unable to access the camera: ", error);
                            alert("Unable to access the camera. Please make sure you've granted permission.");
                        }
                    } else {
                        alert("Sorry, your browser does not support accessing the camera.");
                    }
                },

                async startFaceDetection() {
                    const video = this.$refs.video;
                    
                    this.faceDetectionInterval = setInterval(async () => {
                        const detections = await faceapi.detectAllFaces(
                            video,
                            new faceapi.TinyFaceDetectorOptions()
                        );

                        // Update face detection status
                        this.faceDetected = detections.length > 0;
                    }, 100);
                },

                capturePhoto() {
                    if (!this.faceDetected) {
                        alert("Please ensure your face is visible in the camera");
                        return;
                    }

                    const video = this.$refs.video;
                    const canvas = document.createElement('canvas');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    canvas.getContext('2d').drawImage(video, 0, 0);
                    const imageDataUrl = canvas.toDataURL('image/jpeg');
                    this.$wire.setPhoto(imageDataUrl);
                },

                retakePhoto() {
                    this.$wire.set('photo', null);
                    this.$wire.set('photoPreview', null);
                    this.$wire.set('photoTaken', false);
                    this.startFaceDetection(); // Restart face detection
                },

                submitPresensi() {
                    if (!this.$wire.photoTaken) {
                        alert("Silakan ambil foto terlebih dahulu!");
                        return;
                    }
                    this.$wire.submitPresensi();
                },

                stopCamera() {
                    if (this.stream) {
                        this.stream.getTracks().forEach(track => track.stop());
                    }
                    if (this.faceDetectionInterval) {
                        clearInterval(this.faceDetectionInterval);
                    }
                }
            }
        }

        document.addEventListener('livewire:navigated', () => {
            if (typeof cameraHandler !== 'undefined' && cameraHandler().stopCamera) {
                cameraHandler().stopCamera();
            }
        });
    </script>