const { promisify } = require('util');
const fs = require('fs');

async function removeFile(fileName) {
  const fsUnlinkP = promisify(fs.unlink);
  try {
    await fsUnlinkP(fileName);
  } catch (err) {
    console.error("Something went wrong in removing the file", err);
  }
}

module.exports = {
  removeFile : removeFile
}
