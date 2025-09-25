# Base64 Image Implementation - Pendataan IGD API

This document explains the base64 image encoding and decoding implementation in the Pendataan IGD API using the `melihovv/base64-image-decoder` package.

## Overview

The API now supports base64 image encoding and decoding for escort photo handling, providing a seamless way to upload and retrieve images in JSON format.

## Package Used

- **Package**: `melihovv/base64-image-decoder`
- **Version**: ^0.2.0
- **Purpose**: Reliable base64 image validation, decoding, and processing

## New Features

### 1. Base64 Image Upload in Create Escort

**Endpoint**: `POST /api/escort`

**Request Body**:
```json
{
    "kategori_pengantar": "Perorangan",
    "nama_pengantar": "John Doe",
    "jenis_kelamin": "Laki-laki",
    "nomor_hp": "081234567890",
    "plat_nomor": "B1234XYZ",
    "nama_pasien": "Jane Doe",
    "foto_pengantar_base64": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD...",
    "foto_pengantar_info": {
        "name": "photo.jpg",
        "size": 2048,
        "type": "image/jpeg"
    },
    "status": "pending"
}
```

### 2. Base64 Image in List Response

**Endpoint**: `GET /api/escort?include_images=base64`

Add the `include_images=base64` parameter to include base64 encoded images in the response.

### 3. Base64 Image in Single Escort Response

**Endpoint**: `GET /api/escort/{id}`

Single escort responses automatically include base64 encoded images.

### 4. Base64 Image Update

**Endpoint**: `PUT /api/escort/{id}`

**Request Body**:
```json
{
    "nama_pengantar": "John Doe Updated",
    "foto_pengantar_base64": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD...",
    "foto_pengantar_info": {
        "name": "updated_photo.jpg",
        "size": 2048,
        "type": "image/jpeg"
    }
}
```

### 5. Dedicated Base64 Image Endpoints

#### Get Image as Base64
**Endpoint**: `GET /api/escort/{id}/image/base64`

**Response**:
```json
{
    "status": "success",
    "message": "Image converted to base64 successfully",
    "data": {
        "escort_id": 1,
        "nama_pengantar": "John Doe",
        "image_base64": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD...",
        "mime_type": "image/jpeg",
        "size": 2048,
        "storage_url": "/storage/uploads/filename.jpg"
    }
}
```

#### Upload Image as Base64
**Endpoint**: `POST /api/escort/{id}/image/base64`

**Request Body**:
```json
{
    "foto_pengantar_base64": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD...",
    "foto_pengantar_info": {
        "name": "new_photo.jpg",
        "size": 2048,
        "type": "image/jpeg"
    }
}
```

## Image Validation

The implementation includes comprehensive validation:

- **Supported formats**: JPEG, PNG, JPG, GIF
- **Maximum file size**: 2MB
- **Base64 format validation**: Ensures proper data URI format
- **File extension validation**: Validates against allowed formats
- **Image content validation**: Uses the package's built-in validation

## Error Handling

Common error responses:

### Invalid Format
```json
{
    "status": "error",
    "message": "Format gambar tidak valid atau ukuran terlalu besar. Maksimal 2MB dan format yang didukung: JPEG, PNG, GIF."
}
```

### File Too Large
```json
{
    "status": "error",
    "message": "Gagal memproses gambar: Image size exceeds maximum allowed size"
}
```

### Invalid Base64
```json
{
    "status": "error",
    "message": "Gagal memproses gambar: Invalid base64 image data"
}
```

## Implementation Details

### Controller Methods

1. **`processBase64Image()`**: Private helper method for processing base64 images
2. **`convertImageToBase64()`**: Private helper method for converting stored images to base64
3. **`getImageBase64()`**: Public endpoint for retrieving images as base64
4. **`uploadImageBase64()`**: Public endpoint for uploading images as base64

### Model Enhancements

Added utility methods to `EscortModel`:

- `hasImage()`: Check if escort has an image
- `getImageUrl()`: Get public storage URL
- `getImagePath()`: Get file system path
- `imageExists()`: Verify image file exists

### Session Tracking

The implementation includes comprehensive session tracking:

- File upload statistics
- Image processing metadata
- Error tracking and debugging information

## Security Features

- **File type validation**: Only allows specific image formats
- **Size limitations**: Enforces maximum file size
- **Content validation**: Validates actual image content, not just extension
- **Path sanitization**: Prevents directory traversal attacks
- **Unique filenames**: Prevents filename collisions

## Usage Examples

### JavaScript Frontend Example

```javascript
// Convert file to base64
function fileToBase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = () => resolve(reader.result);
        reader.onerror = error => reject(error);
    });
}

// Upload escort with base64 image
async function createEscort(formData, imageFile) {
    const base64Image = await fileToBase64(imageFile);
    
    const payload = {
        ...formData,
        foto_pengantar_base64: base64Image,
        foto_pengantar_info: {
            name: imageFile.name,
            size: imageFile.size,
            type: imageFile.type
        }
    };
    
    const response = await fetch('/api/escort', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    });
    
    return response.json();
}
```

### PHP Backend Example

```php
// Using the API from another Laravel application
$client = new GuzzleHttp\Client();

$imageData = base64_encode(file_get_contents('/path/to/image.jpg'));
$base64String = 'data:image/jpeg;base64,' . $imageData;

$response = $client->post('http://your-api-domain.com/api/escort', [
    'json' => [
        'kategori_pengantar' => 'Perorangan',
        'nama_pengantar' => 'John Doe',
        'jenis_kelamin' => 'Laki-laki',
        'nomor_hp' => '081234567890',
        'plat_nomor' => 'B1234XYZ',
        'nama_pasien' => 'Jane Doe',
        'foto_pengantar_base64' => $base64String,
        'foto_pengantar_info' => [
            'name' => 'photo.jpg',
            'size' => strlen(file_get_contents('/path/to/image.jpg')),
            'type' => 'image/jpeg'
        ]
    ],
    'headers' => [
        'Accept' => 'application/json'
    ]
]);

$result = json_decode($response->getBody(), true);
```

## Testing

### Using Postman

1. Import the provided Postman collection: `docs/Pendataan_IGD_API_Collection.postman_collection.json`
2. Set the `base_url` variable to your API endpoint
3. Authenticate to get an access token
4. Use the "Base64 Image Operations" folder for testing image functionality

### Base64 Test Data

For testing purposes, you can use this minimal base64 image:

```
data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCdABmX/9k=
```

## Performance Considerations

- Base64 encoding increases data size by approximately 33%
- Large images may impact API response times
- Consider implementing image compression for large files
- Use pagination when including base64 images in list responses
- Cache base64 conversions for frequently accessed images

## Migration Notes

If upgrading from a previous version:

1. The existing file upload functionality remains unchanged
2. Base64 functionality is additive - no breaking changes
3. Update your frontend to optionally use base64 encoding
4. Session tracking includes new image-related metrics

## Troubleshooting

### Common Issues

1. **"Invalid base64 image data"**: Ensure the base64 string includes the proper data URI prefix
2. **"Format tidak didukung"**: Check that the image format is JPEG, PNG, JPG, or GIF
3. **"Ukuran terlalu besar"**: Reduce image size to under 2MB
4. **Storage errors**: Verify storage permissions and disk space

### Debug Information

Check the Laravel logs for detailed error information. The implementation includes comprehensive logging for debugging purposes.