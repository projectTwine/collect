const fs = require('fs');
const removeFile = require('../utils').removeFile;

/*
test('auto transcribe captions for a youtube video without captions', () => {

});



test('get accurate captions for a youtube video with captions', () => {

});
*/


test('should remove a caption file', async() => {
  const makeFileM = jest.fn().mockImplementation((fileName) => {
    fs.closeSync(fs.openSync(fileName, 'w'));
  });
  
  makeFileM("test-file");
  try {
    await removeFile("test-file");
  } catch (err) {
    expect(err).not.toBeDefined();
  }
});



/*
test('should write YouTube attributes accurately to database', () => {

});
*/
