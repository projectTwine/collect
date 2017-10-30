const algorithmia = require("algorithmia");
const client = algorithmia("simn+5XuYZDYrQlxlZR+z756gVX1"); //TODO extract API key
const fs = require('fs');
const text = fs.readFile('script-2.txt', 'utf8', (err, data) => {
  client.algo("algo://nlp/AutoTag/1.0.1")
        .pipe(data)
        .then((res) => {
          console.log(res.get());
        })
});

/*
fs.readFile('script-2.txt', 'utf8', (err, data) => {
  console.log("CHUNK:", data);
});
*/
