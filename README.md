# ComplexMap

# Description

Traversable class used to support situations where a standard PHP array needs to be able to map more 
than just scalar keys to array values and instead use more complex values as the array keys.

For instance a use case for this would be where you need to map an instance of a class (let's say a UserModel) to 
another value like perhaps a collection of that users Posts. Normally you would be forced to use a unique value 
like an ID as the array key and as a result have to go and fetch the UserModel by id later on somewhere in your code. 

Using this map you would still have access to the User object reference as the Map key. Literally allows you to map 
anything to anything else for extra flexibility.
