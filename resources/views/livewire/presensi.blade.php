<div class="bg-gradient-to-r from-blue-100 to-purple-100 min-h-screen py-12">
    <div class="container mx-auto max-w-5xl px-4">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="p-8">
                <h1 class="text-4xl font-bold text-gray-800 mb-8 text-center">Informasi Presensi</h1>

                @if (!$showPhotoUploadPage)
                    <!-- Employee Information -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 mb-8 shadow-md">
                        <h2 class="text-2xl font-semibold text-indigo-800 mb-4">Informasi Pegawai</h2>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <p class="text-gray-700"><span class="font-medium text-indigo-600">Nama Pegawai:</span>
                                    {{Auth::user()->name}}</p>
                                <p class="text-gray-700"><span class="font-medium text-indigo-600">Kantor:</span>
                                    {{$schedule->office->nama}}</p>
                            </div>
                            <div class="space-y-2">
                                <p class="text-gray-700"><span class="font-medium text-indigo-600">Shift:</span>
                                    {{$schedule->shift->nama}}
                                    ({{$schedule->shift->waktu_datang}} - {{$schedule->shift->waktu_pulang}} WIB)</p>
                                <p class="text-gray-700">
                                    <span class="font-medium text-indigo-600">Status:</span>
                                    @if ($schedule->is_wfa)
                                        <span class="text-blue-600 font-semibold bg-blue-100 px-2 py-1 rounded-full">WFA</span>
                                    @else
                                        <span
                                            class="text-green-600 font-semibold bg-green-100 px-2 py-1 rounded-full">WFO</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Times -->
                    <div class="grid md:grid-cols-2 gap-6 mb-8">
                        <div
                            class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-6 shadow-md transition duration-300 hover:shadow-lg">
                            <h3 class="text-xl font-semibold text-emerald-800 mb-2">Waktu Masuk</h3>
                            <p class="text-3xl font-bold text-emerald-600">{{$attendance ? $attendance->waktu_datang : '-'}}
                            </p>
                        </div>
                        <div
                            class="bg-gradient-to-r from-red-50 to-rose-50 rounded-xl p-6 shadow-md transition duration-300 hover:shadow-lg">
                            <h3 class="text-xl font-semibold text-rose-800 mb-2">Waktu Pulang</h3>
                            <p class="text-3xl font-bold text-rose-600">
                                {{$attendance && $attendance->waktu_pulang ? $attendance->waktu_pulang : '-'}}
                            </p>
                        </div>
                    </div>

                    <!-- Map and Attendance Form -->
                    <div class="bg-gradient-to-r from-gray-50 to-slate-50 rounded-xl p-6 shadow-md">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Presensi</h2>
                        <div id="map" class="w-full h-96 rounded-lg border-2 border-gray-300 mb-6 shadow-inner" wire:ignore>
                        </div>

                        <!-- Messages -->
                        @if (session()->has('message'))
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                                role="alert">
                                <span class="block sm:inline">{{ session('message') }}</span>
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                                role="alert">
                                <span class="block sm:inline">{{ session('error') }}</span>
                            </div>
                        @endif

                        <form class="flex flex-col sm:flex-row gap-4" wire:submit.prevent="initiateAttendance">
                            <button type="button" onclick="tagLocation()"
                                class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg transition duration-300 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-50 shadow-md">
                                Ambil Lokasi
                            </button>
                            @if ($insideRadius)
                                <button
                                    class="px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition duration-300 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-opacity-50 shadow-md"
                                    type="submit">
                                    Lanjut ke Foto
                                </button>
                            @endif
                        </form>
                    </div>
                @else
                    <!-- Photo Upload Section -->
                    <div x-data="cameraHandler()" x-init="initializeCamera">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Ambil Foto Presensi</h2>
                        <div class="mb-4 relative">
                            <video x-show="!photoTaken" x-ref="video" width="100%" height="auto" autoplay playsinline
                                class="rounded-lg shadow-md"></video>
                            <img x-show="photoTaken" :src="photoPreview" alt="Preview"
                                class="w-full h-auto rounded-lg shadow-md">
                        </div>
                        <div class="flex justify-between mb-4">
                            <button x-show="!photoTaken" @click="capturePhoto"
                                class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg transition duration-300 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-50 shadow-md">
                                Ambil Foto
                            </button>
                            <button x-show="photoTaken" @click="retakePhoto"
                                class="px-6 py-3 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg transition duration-300 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-opacity-50 shadow-md">
                                Ambil Ulang
                            </button>
                            @if ($showLogbookForm)
                                <div class="mt-4">
                                    <h3 class="text-lg font-semibold mb-2">Logbook Harian</h3>
                                    <textarea wire:model="logbook" class="w-full h-32 p-2 border rounded"
                                        placeholder="Tulis deskripsi pekerjaan Anda hari ini..."></textarea>
                                    @error('logbook') <span class="text-red-500">{{ $message }}</span> @enderror
                                </div>
                            @endif
                            <button x-show="photoTaken" @click="submitPresensi"
                                class="px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition duration-300 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-opacity-50 shadow-md">
                                {{ $showLogbookForm ? 'Submit Presensi dan Logbook' : 'Submit Presensi' }}
                            </button>
                        </div>
                        <button wire:click="backToMap"
                            class="w-full px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg transition duration-300 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-opacity-50 shadow-md">
                            Kembali ke Peta
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
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
            photoTaken: false,
            photoPreview: null,
            initializeCamera() {
                if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                    navigator.mediaDevices.getUserMedia({ video: true })
                        .then(stream => {
                            this.stream = stream;
                            this.$refs.video.srcObject = stream;
                        })
                        .catch(error => {
                            console.error("Unable to access the camera: ", error);
                            alert("Unable to access the camera. Please make sure you've granted permission.");
                        });
                } else {
                    alert("Sorry, your browser does not support accessing the camera.");
                }
            },
            capturePhoto() {
                const video = this.$refs.video;
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                const imageDataUrl = canvas.toDataURL('image/jpeg');
                this.photoPreview = imageDataUrl;
                this.photoTaken = true;
                @this.set('photo', imageDataUrl);
                @this.set('photoPreview', imageDataUrl);
            },
            retakePhoto() {
                this.photoTaken = false;
                this.photoPreview = null;
                @this.set('photo', null);
                @this.set('photoPreview', null);
            },
            submitPresensi() {
                if (!this.photoTaken) {
                    alert("Silakan ambil foto terlebih dahulu!");
                    return;
                }
                @this.call('submitPresensi');
            },
            stopCamera() {
                if (this.stream) {
                    this.stream.getTracks().forEach(track => track.stop());
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