yr=input("enter a yr")
if (yr%100==0 and yr%400==0 and yr%4==0):
    print("leap yr")
else:
     print("not a leap yr")