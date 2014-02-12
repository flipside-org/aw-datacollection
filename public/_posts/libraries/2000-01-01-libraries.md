---
layout: default
title: Libraries
parent: Libraries
top_level: true
---

### Modified Libraries

Follows a list of libraries used in the project that required some modifications.

#### MongoQB
- [https://github.com/alexbilbie/MongoQB](https://github.com/alexbilbie/MongoQB)
- Query builder for mongo DB. Originally the author build this library specifically for codeigniter but later modified it making it framework agnostic. The codeigniter one is still available but its outdated, so the new one was adapted to integrate seamlessly with codeigniter again.

#### CodeIgniter MongoDB Session Library
- [https://github.com/sepehr/ci-mongodb-session](https://github.com/sepehr/ci-mongodb-session)
- MongoDB Session Library extends codeigniter session allowing it to use a mongo backend. This library required the MongoDB query builder for codeigniter but, since we are using a modified version of it, this library needed to be modified as well.
