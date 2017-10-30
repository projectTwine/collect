#/bin/bash -xe
curl -X GET \
  'https://www.googleapis.com/youtube/v3/videos?id=8aGhZQkoFbQ&part=snippet%2Cstatistics&key=AIzaSyC8LjcCWH62R3lw_P6F__B6W2GLwa9OvyI' \
  -H 'cache-control: no-cache' \
  -H 'postman-token: cf5ddffc-53fa-0dc9-71b1-0b4835ff2839'
