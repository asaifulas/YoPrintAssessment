<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>CSV Upload</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.0/js/dataTables.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.0/css/dataTables.dataTables.css" />

    @vite(['resources/js/app.js'])
</head>

<body class="bg-gray-100 text-gray-800 min-h-screen flex flex-col items-center p-8">

    <div class="w-full max-w-4xl bg-white shadow-md rounded-lg p-6">
        <div>
            <h1 class="text-2xl font-bold">CSV Upload - YoPrint Assessment</h1>
            <h3 class="text-sm text-gray-500 mb-4">Ahmad Saifullah Arifin</h3>
            <h2 class="text-xl font-semibold mb-4">Upload File</h2>

            <form method="POST" action="{{ route('upload') }}" id="uploadForm" enctype="multipart/form-data"
                class="flex items-center gap-4">
                @csrf
                <input type="file" name="csv_file" id="csv_file" accept=".csv" required class="hidden" />

                <label for="csv_file"
                    class="flex justify-between items-center w-full p-6 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition">

                    <div id="file-label">
                        <p class="text-gray-700 font-semibold">Select or drag and drop your file here</p>
                        <p class="text-sm text-gray-400 mt-1">Only CSV files are allowed</p>
                    </div>

                    <button type="submit"
                        class="bg-blue-600 text-white text-sm font-medium px-5 py-2.5 rounded hover:bg-blue-700 transition">
                        Upload
                    </button>
                </label>
            </form>
        </div>

        <div class="mt-5">
            <h2 class="text-xl font-semibold mb-4">Uploaded Files</h2>
            <div class="overflow-x-auto">
                <table id="tableUploads" class="w-full table-auto border-collapse">
                    <thead>
                        <tr class="bg-gray-200 text-left">
                            <th class="px-4 py-2 border">Time</th>
                            <th class="px-4 py-2 border">File Name</th>
                            <th class="px-4 py-2 border">Status</th>
                        </tr>
                    </thead>
                    <tbody id="upload-list">
                        @foreach ($uploads as $upload)
                            <tr data-id="{{ $upload->id }}" class="border-t">
                                <td id="uploadTime-{{ $upload->id }}" class="px-4 py-2 border">
                                    {{ $upload->uploaded_at ?? $upload->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-2 border">{{ basename($upload->filename) }}</td>
                                <td class="px-4 py-2 border" id="status-{{ $upload->id }}">
                                    {{ $upload->status }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            new DataTable('#tableUploads', {
                order: [
                    [0, 'desc']
                ]
            });

            const fileInput = document.getElementById('csv_file');
            const fileLabel = document.getElementById('file-label');

            fileInput.addEventListener('change', function() {
                if (fileInput.files.length > 0) {
                    fileLabel.innerHTML = `
                        <p class="text-gray-700 font-semibold">File Name: ${fileInput.files[0].name}</p>
                    `;
                } else {
                    fileLabel.innerHTML = `
                        <p class="text-gray-700 font-semibold">Select or drag and drop your file here</p>
                        <p class="text-sm text-gray-400 mt-1">Only CSV files are allowed</p>
                    `;
                }
            });

            const uploads = @json($uploads);

            uploads.map(upload => {
                const statusText = document.getElementById('status-' + upload.id);
                const uploadTime = document.getElementById('uploadTime-' + upload.id);

                const createdAt = new Date(upload.created_at);
                const formattedDateTime = createdAt.toLocaleString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true,
                });
                const now = new Date();
                const diffInMinutes = Math.floor((now - createdAt) / 1000 / 60);

                if (diffInMinutes < 60) {
                    const timer = setInterval(updateTime, 60000);
                    updateTime();

                    function updateTime() {
                        const current = new Date();
                        const minutesAgo = Math.floor((current - createdAt) / 1000 / 60);

                        if (minutesAgo >= 60) {
                            clearInterval(timer);
                            uploadTime.innerHTML = formattedDateTime;
                        } else {
                            uploadTime.innerHTML = formattedDateTime +
                                `<br>${minutesAgo} minute${minutesAgo !== 1 ? 's' : ''} ago`;
                        }
                    }
                } else {
                    uploadTime.innerHTML = formattedDateTime;
                }

                if (upload.status === 'pending' || upload.status === 'processing') {

                    window.Echo.channel(`upload-progress.${upload.id}`)
                        .listen('UploadProgressEvent', (e) => {
                            console.log(e)
                            statusText.textContent = e.progress
                        });

                }
            });
        })
    </script>
</body>

</html>
