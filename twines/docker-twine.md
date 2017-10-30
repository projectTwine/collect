Some thoughts from the creator:

Making these has cleared up a LOT about what i need. The embeddable feature seems to be pretty necessary.
I can test this twine out again using a non-typeform setting and just using invisionapp with my designs. But excessive clicks (my hypothesis) can
be distracting.

Current design doesnt account for a lot of things and also has things that can be removed. The intro video is up for questioning
* No embeddable short/long answer questioning
* Video playing within the twine
* As it stands the twine design is not meant for going through but more for seeing the syllabus. We likely need a separate view 
for actually running through the twine.


##A Docker Overview
Watch: https://training.docker.com/docker-fundamentals (this is the best video of many that I went through)

Watch video from 0:00 - 7:28 (do the exercise)

###Questions
Q: What's a base image?
Q: How does copy on write work in docker? Why is this optimal?
Read : https://en.wikipedia.org/wiki/Copy-on-write
Q: In the example, you installed curl onto the image. Explain how curl was installed onto the image to create a new image via layering.

Watch: 7:28 - 26:18 of main video

##How are containers different from virtual machines?
Read: https://en.wikipedia.org/wiki/Cgroups
Read first answer: https://stackoverflow.com/questions/16047306/how-is-docker-different-from-a-normal-virtual-machine 
Q: At this point ask yourself, "how was docker engineered using cgroups and namespaces"? Put your theory down below

Q: Give a usecase where it could be useful to specify a CMD inside a dockerfile for a database you setup.

Watch: 26:19 - 38:39 of main video
Q: How is it possible that $docker push is so fast if it pushes full containers to the web?

Watch: 38:39 - 44:22
Note: Containers are meant to be ephemeral -- this idea stems from the 12 factor methodology, a philosophy that a lot of devops engineers use. It's not important that you know all of 12factor, but important that you see how it could impact Docker best practices.
Read (2 min): https://12factor.net/processes
Optional Read (1 min): https://www.blackpepper.co.uk/what-we-think/blog/docker-unix-philosophy
Q: How does the 12 factor methodology processes section relate to creating volumes separately from storing on-image? 

44:22 to end
Q: What is the point of a /etc/hosts file?

Read: https://docs.docker.com/engine/userguide/eng-image/dockerfile_best-practices/#general-guidelines-and-recommendations

Exercise: I want to design Handshake's architecture, it relies on a separate web app running per school that handshake is in. The web app runs in PHP and uses a relational database, mySQL for storage. It uses Redis for faster caching and hosts everything on DigitalOcean. Additionally, every 3 days they run a scraper to find all additional job listings. Explain how you would containerize this app. How many containers will there be?  Explain your decision making.


