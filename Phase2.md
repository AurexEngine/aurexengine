What's the next thing you want to tackle? A few natural directions from here:

Model relationships — hasMany, belongsTo, hasOne on the base Model
Validation — a Validator class so controllers can validate request input cleanly before hitting the DB
Error handling — a proper exception handler in the HTTP kernel that catches HttpException and renders nice error pages/JSON instead of a PHP fatal
make:migration CLI command — right now migrations have to be written manually with no scaffold
Testing — PHPUnit test coverage for the pieces already built, starting with Builder and RouteCollection since those had bugs