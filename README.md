# Aws_Sync_Files

## Description
Aws_Sync_Files is a WordPress plugin designed to seamlessly sync all images uploaded to your WordPress site with an Amazon S3 bucket. It automatically replaces the local image URLs with their corresponding URLs from the S3 bucket, ensuring efficient management of media files and optimized performance for your WordPress site.

## Features
- Syncs all uploaded images to an Amazon S3 bucket.
- Automatically updates image URLs to point to the synced images in the S3 bucket.
- Enhances website performance by offloading image delivery to Amazon S3.
- Easy configuration through the WordPress admin interface.

## Installation
1. Download the latest version of the plugin ZIP file from the [releases page](https://github.com/vallarasuk/Aws_Sync_Files/releases).
2. Upload the ZIP file to your WordPress site and activate the plugin.
3. Configure the AWS credentials and bucket details in the plugin settings.

## Usage
Once the plugin is activated and configured, it automatically syncs all uploaded images to the specified Amazon S3 bucket. You can continue to upload images as usual through the WordPress media library, and the plugin takes care of the synchronization process in the background.

## Configuration
To configure the plugin, navigate to `Settings > AWS Config` in your WordPress admin dashboard. Enter your AWS access key ID, secret access key, bucket name, and region in the provided fields. Ensure that the AWS credentials have sufficient permissions to access the specified S3 bucket.

## Contributing
Contributions are welcome! If you encounter any issues or have suggestions for improvements, please feel free to open an issue or submit a pull request on GitHub.

## License
This project is licensed under the GNU General Public License v2.0 - see the [LICENSE](LICENSE) file for details.

## Support
For support or inquiries, please contact [Vallarasu K](https://vallarasuk.com/).
